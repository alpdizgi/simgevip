<?php

namespace App\Filament\Resources\MenuResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\MenuResource;
use App\Models\Category;
use App\Models\Menu;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

class ListMenus extends Page
{
    protected static string $resource = MenuResource::class;

    protected static string $view = 'filament.resources.menu-resource.pages.list-menus';

    public function mount(): void
    {
        static::authorizeResourceAccess();
    }

    public function getHeading(): string | Htmlable
    {
        return 'Menü Yönetimi';
    }

    public function getSubheading(): string | Htmlable | null
    {
        return 'Navbar’daki menüler burada listelenir. Satırları sürükleyerek sıralayabilir, düzenleyebilir veya silebilirsiniz.';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\MenuResource\Widgets\MenuStatsWidget::class,
        ];
    }

    protected function getActions(): array
    {
        return [
            Actions\Action::make('createCategory')
                ->label('Yeni menü / kategori')
                ->url(CategoryResource::getUrl('create'))
                ->icon('heroicon-o-plus'),
            Actions\Action::make('createExtra')
                ->label('Ekstra sabit link')
                ->url(MenuResource::getUrl('create'))
                ->color('secondary')
                ->icon('heroicon-o-link'),
        ];
    }

    protected function getViewData(): array
    {
        return [
            'roots' => Category::query()
                ->roots()
                ->with([
                    'children' => fn ($q) => $q
                        ->withCount('products')
                        ->orderBy('sort_order')
                        ->orderBy('name'),
                ])
                ->withCount(['products', 'children'])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'extraMenus' => Menu::query()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(),
        ];
    }

    public function reorderRoots(array $order): void
    {
        $this->applyOrder(collect($order), parentId: null);
    }

    public function reorderChildren(int $parentId, array $order): void
    {
        $this->applyOrder(collect($order), parentId: $parentId);
    }

    public function reorderExtraMenus(array $order): void
    {
        foreach (collect($order)->values() as $index => $id) {
            Menu::query()->whereKey($id)->update(['sort_order' => $index + 1]);
        }
    }

    public function deleteCategory(int $categoryId): void
    {
        $category = Category::query()
            ->withCount(['products', 'children'])
            ->findOrFail($categoryId);

        if ($category->products_count > 0) {
            Notification::make()
                ->title('Silinemedi')
                ->body('Bu menüye bağlı ürünler var. Önce ürünleri taşıyın.')
                ->danger()
                ->send();

            return;
        }

        if ($category->children_count > 0) {
            Notification::make()
                ->title('Silinemedi')
                ->body('Önce alt menüleri silin veya taşıyın.')
                ->danger()
                ->send();

            return;
        }

        $category->delete();

        Notification::make()
            ->title('Menü silindi')
            ->success()
            ->send();

        $this->redirect(MenuResource::getUrl());
    }

    public function deleteExtraMenu(int $menuId): void
    {
        Menu::query()->whereKey($menuId)->delete();

        Notification::make()
            ->title('Ekstra link silindi')
            ->success()
            ->send();

        $this->redirect(MenuResource::getUrl());
    }

    public function toggleCategoryNav(int $categoryId): void
    {
        $category = Category::query()->findOrFail($categoryId);
        $category->show_in_nav = ! $category->show_in_nav;
        $category->save();

        $this->redirect(MenuResource::getUrl());
    }

    public function toggleExtraMenu(int $menuId): void
    {
        $menu = Menu::query()->findOrFail($menuId);
        $menu->is_active = ! $menu->is_active;
        $menu->save();

        $this->redirect(MenuResource::getUrl());
    }

    private function applyOrder(Collection $order, ?int $parentId): void
    {
        $ids = $order
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        foreach ($ids as $index => $id) {
            Category::query()
                ->whereKey($id)
                ->when(
                    $parentId === null,
                    fn ($q) => $q->whereNull('parent_id'),
                    fn ($q) => $q->where('parent_id', $parentId),
                )
                ->update(['sort_order' => $index + 1]);
        }
    }
}
