<x-dynamic-component
    :component="$getFieldWrapperView()"
    :id="$getId()"
    :label="$getLabel()"
    :label-sr-only="$isLabelHidden()"
    :helper-text="$getHelperText()"
    :hint="$getHint()"
    :hint-action="$getHintAction()"
    :hint-color="$getHintColor()"
    :hint-icon="$getHintIcon()"
    :state-path="$getStatePath()"
>
    <p class="text-sm text-gray-500 dark:text-gray-400">
        Ürün özellik alanlarını yönetmek için sağdaki butonu kullanın.
    </p>
</x-dynamic-component>
