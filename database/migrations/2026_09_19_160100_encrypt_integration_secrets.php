<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EncryptIntegrationSecrets extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('integrations')) {
            return;
        }

        DB::table('integrations')->orderBy('id')->chunkById(50, function ($rows) {
            foreach ($rows as $row) {
                $update = [];

                foreach (['api_key', 'api_secret', 'config'] as $field) {
                    $value = $row->{$field} ?? null;

                    if ($value === null || $value === '') {
                        continue;
                    }

                    try {
                        Crypt::decryptString($value);
                    } catch (\Throwable $e) {
                        $update[$field] = Crypt::encryptString($value);
                    }
                }

                if ($update !== []) {
                    DB::table('integrations')->where('id', $row->id)->update($update);
                }
            }
        });
    }

    public function down()
    {
        if (! Schema::hasTable('integrations')) {
            return;
        }

        DB::table('integrations')->orderBy('id')->chunkById(50, function ($rows) {
            foreach ($rows as $row) {
                $update = [];

                foreach (['api_key', 'api_secret', 'config'] as $field) {
                    $value = $row->{$field} ?? null;

                    if ($value === null || $value === '') {
                        continue;
                    }

                    try {
                        $update[$field] = Crypt::decryptString($value);
                    } catch (\Throwable $e) {
                        // already plaintext
                    }
                }

                if ($update !== []) {
                    DB::table('integrations')->where('id', $row->id)->update($update);
                }
            }
        });
    }
}
