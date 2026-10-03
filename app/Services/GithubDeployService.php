<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\Process;

class GithubDeployService
{
    public function isEnabled(): bool
    {
        return (bool) config('deploy.enabled');
    }

    /**
     * Canlıya gidecek değişmiş dosyaları listeler; push yapmaz.
     *
     * @return array{files: list<string>, catalog: bool, message: string, issues: list<string>}
     */
    public function preview(): array
    {
        $this->assertLocal();

        $catalog = false;
        if (config('deploy.include_catalog')) {
            $this->exportCatalog();
            $catalog = true;
        }

        $this->ensureRepository((string) config('deploy.branch', 'main'));
        $files = $this->stageAllowedChanges();

        if ($files !== []) {
            $this->git(['reset', '-q'], true);
        }

        $issues = $this->packageHealthIssues($files);
        $count = count($files);
        $message = $files === []
            ? 'Canlıya gönderilecek yeni değişiklik yok.'
            : ($this->hasHead()
                ? $count . ' değişmiş dosya canlı paket listesinde.'
                : $count . ' dosya ilk canlı paketine girecek (henüz Git kaydı yok; sonraki gönderilerde yalnızca değişenler listelenir).');

        if ($issues !== []) {
            $message .= ' Kontrol uyarısı: ' . implode(' ', $issues);
        }

        return [
            'files' => $files,
            'catalog' => $catalog,
            'message' => $message,
            'issues' => $issues,
        ];
    }

    public function run(): string
    {
        $this->assertLocal();

        if (! $this->isEnabled()) {
            throw new RuntimeException('Canlı aktarım henüz açık değil. Hazır olunca .env dosyasına DEPLOY_ENABLED=true yazın.');
        }

        $repository = trim((string) config('deploy.repository'));
        $token = trim((string) config('deploy.token'));
        $branch = trim((string) config('deploy.branch', 'main')) ?: 'main';

        if ($repository === '' || $token === '') {
            throw new RuntimeException('Canlı aktarım için .env dosyasına DEPLOY_GITHUB_REPOSITORY ve DEPLOY_GITHUB_TOKEN yazın. Depoda da DEPLOY_HOST, DEPLOY_USER, DEPLOY_SSH_KEY ve DEPLOY_PATH sırları tanımlı olmalıdır.');
        }

        $lock = Cache::lock('github-deploy', 600);
        if (! $lock->get()) {
            throw new RuntimeException('Bir aktarım zaten sürüyor. Bitmesini bekleyin.');
        }

        try {
            $catalogNote = null;
            if (config('deploy.include_catalog')) {
                $catalogNote = $this->exportCatalog();
            }

            $this->ensureRepository($branch);
            $files = $this->stageAllowedChanges();
            $issues = $this->packageHealthIssues($files);
            if ($issues !== []) {
                $this->git(['reset', '-q'], true);
                throw new RuntimeException('Canlı paket kontrolü başarısız: ' . implode(' ', $issues));
            }
            $committed = $this->commitStaged($files);

            if (! $this->hasHead()) {
                throw new RuntimeException('Gönderilecek paket oluşmadı. Allowlist içinde commit edilecek dosya yok.');
            }

            $this->push($repository, $token, $branch);

            if (! $committed) {
                return 'Canlıya gidecek yeni dosya değişikliği yoktu. Mevcut kayıt GitHub\'a iletildi.';
            }

            $sample = collect($files)->take(8)->implode(', ');
            $more = count($files) > 8 ? ' …' : '';

            return 'Yalnızca canlı için gerekli '
                . count($files)
                . ' dosya GitHub\'a gönderildi'
                . ($catalogNote ? ' (' . $catalogNote . ')' : '')
                . '. Örnek: '
                . $sample
                . $more
                . '. Müşteri, sipariş ve destek kayıtları canlıda durur.';
        } finally {
            $lock->release();
        }
    }

    private function assertLocal(): void
    {
        if (! app()->environment('local')) {
            throw new RuntimeException('Bu düğme yalnızca yerel yönetim panelinde çalışır.');
        }
    }

    private function exportCatalog(): string
    {
        $database = (string) config('database.connections.mysql.database');
        $tables = collect(DB::select('SHOW TABLES'))
            ->map(function ($row) {
                return (string) array_values((array) $row)[0];
            })
            ->reject(function (string $table) {
                return in_array($table, config('deploy.preserve_tables', []), true);
            })
            ->values()
            ->all();

        if ($tables === []) {
            throw new RuntimeException('Aktarılacak veritabanı tablosu bulunamadı.');
        }

        $directory = database_path('deploy');
        File::ensureDirectoryExists($directory);
        $target = $directory . DIRECTORY_SEPARATOR . 'catalog.sql';
        $cnf = storage_path('app/deploy-mysql.cnf');

        File::put($cnf, $this->mysqlDefaults());

        try {
            $dump = $this->mysqldump($cnf, $database, $tables);
        } finally {
            File::delete($cnf);
        }

        foreach (config('deploy.preserve_tables', []) as $preserved) {
            $pattern = '/\b(CREATE|DROP|INSERT|REPLACE|ALTER|UPDATE|DELETE)\b[^;\n]*`' . preg_quote((string) $preserved, '/') . '`/i';
            if (preg_match($pattern, $dump)) {
                throw new RuntimeException('Katalog dışa aktarımında korunması gereken tablo sızdı: ' . $preserved);
            }
        }

        File::put($target, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n" . $dump . "\nSET FOREIGN_KEY_CHECKS=1;\n");

        return count($tables) . ' tablo';
    }

    private function mysqlDefaults(): string
    {
        $connection = config('database.connections.mysql');
        $quote = function ($value) {
            return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], (string) $value) . '"';
        };

        return "[client]\n"
            . 'user=' . $quote($connection['username'] ?? '') . "\n"
            . 'password=' . $quote($connection['password'] ?? '') . "\n"
            . 'host=' . $quote($connection['host'] ?? '127.0.0.1') . "\n"
            . 'port=' . (int) ($connection['port'] ?? 3306) . "\n";
    }

    private function mysqldump(string $cnf, string $database, array $tables): string
    {
        $binary = (string) config('deploy.mysqldump');
        if (! is_file($binary)) {
            $binary = 'mysqldump';
        }

        $command = array_merge([
            $binary,
            '--defaults-extra-file=' . $cnf,
            '--single-transaction',
            '--add-drop-table',
            '--default-character-set=utf8mb4',
            '--no-tablespaces',
            $database,
        ], $tables);

        try {
            return $this->execute($command);
        } catch (RuntimeException $exception) {
            if (! str_contains($exception->getMessage(), 'no-tablespaces')) {
                throw $exception;
            }

            $command = array_values(array_filter($command, function ($part) {
                return $part !== '--no-tablespaces';
            }));

            return $this->execute($command);
        }
    }

    private function ensureRepository(string $branch): void
    {
        if (is_dir(base_path('.git'))) {
            return;
        }

        try {
            $this->git(['init', '-b', $branch]);
        } catch (RuntimeException $exception) {
            $this->git(['init']);
            $this->git(['checkout', '-b', $branch], true);
            if ($this->git(['branch', '--show-current'], true) === '') {
                $this->git(['symbolic-ref', 'HEAD', 'refs/heads/' . $branch], true);
            }
        }
    }

    private function hasHead(): bool
    {
        $process = new Process(['git', 'rev-parse', '--verify', 'HEAD'], base_path());
        $process->setTimeout(30);
        $process->run();

        return $process->isSuccessful();
    }

    /**
     * Yalnızca allowlist'teki değişmiş dosyaları stage eder.
     *
     * @return list<string>
     */
    private function stageAllowedChanges(): array
    {
        $this->git(['reset', '-q'], true);

        $pathspecs = $this->existingIncludePaths();
        if ($pathspecs === []) {
            throw new RuntimeException('Canlı paket listesinde geçerli bir yol bulunamadı. config/deploy.php içindeki include_paths listesini kontrol edin.');
        }

        $this->git(array_merge(['add', '-A', '--'], $pathspecs));

        foreach ($this->existingExcludePaths() as $excluded) {
            $this->git(['reset', '-q', '--', $excluded], true);
        }

        $staged = $this->git(['diff', '--cached', '--name-only', '-z']);
        if ($staged === '') {
            return [];
        }

        $files = array_values(array_filter(explode("\0", $staged)));
        $files = array_values(array_filter($files, function (string $file) {
            return $this->isDeployable($file);
        }));

        if ($files === []) {
            $this->git(['reset', '-q'], true);

            return [];
        }

        $this->git(['reset', '-q'], true);
        foreach (array_chunk($files, 40) as $chunk) {
            $this->git(array_merge(['add', '-A', '--'], $chunk));
        }

        return $files;
    }

    /**
     * @param  list<string>  $files
     */
    private function commitStaged(array $files): bool
    {
        if ($files === [] || $this->git(['diff', '--cached', '--name-only']) === '') {
            return false;
        }

        $summary = count($files) . ' dosya';
        $this->git([
            '-c', 'user.name=SimgeVIP Deploy',
            '-c', 'user.email=deploy@simgevip.local',
            'commit',
            '-m',
            'Canlı paket: ' . $summary,
        ]);

        return true;
    }

    private function isDeployable(string $path): bool
    {
        $normalized = str_replace('\\', '/', ltrim($path, '/'));

        if ($this->isSecretEnvPath($normalized)) {
            return false;
        }

        foreach (explode('/', $normalized) as $segment) {
            if ($segment !== '' && $this->matchesExcludedName($segment)) {
                return false;
            }
        }

        foreach (config('deploy.exclude_paths', []) as $excluded) {
            $excluded = str_replace('\\', '/', trim((string) $excluded, '/'));
            if ($excluded === '') {
                continue;
            }
            if ($normalized === $excluded || str_starts_with($normalized, $excluded . '/')) {
                return false;
            }
        }

        foreach (config('deploy.include_paths', []) as $included) {
            $included = str_replace('\\', '/', trim((string) $included, '/'));
            if ($included === '') {
                continue;
            }
            if ($normalized === $included || str_starts_with($normalized, $included . '/')) {
                return true;
            }
        }

        return false;
    }

    private function isSecretEnvPath(string $normalized): bool
    {
        if ($normalized === '.env.example') {
            return false;
        }

        return $normalized === '.env' || str_starts_with($normalized, '.env.');
    }

    /**
     * @param  list<string>  $files
     * @return list<string>
     */
    private function packageHealthIssues(array $files): array
    {
        $issues = [];

        foreach ($files as $file) {
            if (! $this->isDeployable($file)) {
                $issues[] = $file . ' pakete sızmış (filtre hatası).';
            }
        }

        foreach (config('deploy.required_structure_files', []) as $required) {
            $required = str_replace('\\', '/', (string) $required);
            if ($required === '') {
                continue;
            }
            if (! file_exists(base_path($required))) {
                $issues[] = $required . ' diskte yok.';
                continue;
            }
            if (! $this->isDeployable($required)) {
                $issues[] = $required . ' allowlist dışında; canlı iskelet kırılır.';
            }
        }

        return $issues;
    }

    private function matchesExcludedName(string $name): bool
    {
        $flags = defined('FNM_CASEFOLD') ? FNM_CASEFOLD : 0;

        foreach (config('deploy.exclude_name_patterns', []) as $pattern) {
            if (fnmatch((string) $pattern, $name, $flags)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function existingIncludePaths(): array
    {
        return collect(config('deploy.include_paths', []))
            ->map(fn ($path) => str_replace('\\', '/', (string) $path))
            ->filter(fn (string $path) => $path !== '' && file_exists(base_path($path)))
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    private function existingExcludePaths(): array
    {
        return collect(config('deploy.exclude_paths', []))
            ->map(fn ($path) => str_replace('\\', '/', (string) $path))
            ->filter(fn (string $path) => $path !== '' && file_exists(base_path($path)))
            ->values()
            ->all();
    }

    private function push(string $repository, string $token, string $branch): void
    {
        $this->execute(['git', 'rev-parse', '--verify', 'HEAD']);
        $this->git(['push', $this->authenticatedUrl($repository, $token), 'HEAD:' . $branch]);
    }

    private function authenticatedUrl(string $repository, string $token): string
    {
        $repository = preg_replace('#^git@github\.com:#', 'https://github.com/', trim($repository)) ?? trim($repository);
        $repository = rtrim($repository, '/');
        $repository = preg_replace('#\.git$#', '', $repository) ?? $repository;

        if (! preg_match('#^https://github\.com/([^/\s]+)/([^/\s]+)$#', $repository, $matches)) {
            throw new RuntimeException('DEPLOY_GITHUB_REPOSITORY adresi https://github.com/kullanici/depo biçiminde olmalı.');
        }

        return 'https://x-access-token:' . rawurlencode($token) . '@github.com/' . $matches[1] . '/' . $matches[2] . '.git';
    }

    private function git(array $arguments, bool $allowFailure = false): string
    {
        return $this->execute(array_merge(['git'], $arguments), $allowFailure);
    }

    private function execute(array $command, bool $allowFailure = false): string
    {
        $process = new Process($command, base_path());
        $process->setTimeout(900);
        $process->run();

        $stdout = trim($process->getOutput());
        $combined = trim($process->getOutput() . "\n" . $process->getErrorOutput());
        if (! $process->isSuccessful()) {
            if ($allowFailure) {
                return '';
            }

            throw new RuntimeException($this->redact($combined !== '' ? $combined : 'Komut tamamlanamadı.'));
        }

        return $stdout;
    }

    private function redact(string $text): string
    {
        $token = trim((string) config('deploy.token'));
        if ($token !== '') {
            $text = str_replace([$token, rawurlencode($token)], '[gizli]', $text);
        }

        return $text;
    }
}
