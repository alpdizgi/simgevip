
<script>
    (function () {
        function sanitizeDanglingLabels() {
            var labels = document.querySelectorAll('label[for]');
            for (var i = 0; i < labels.length; i++) {
                var label = labels[i];
                var forId = label.getAttribute('for');
                if (forId && !document.getElementById(forId)) {
                    label.removeAttribute('for');
                }
            }
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', sanitizeDanglingLabels);
        } else {
            sanitizeDanglingLabels();
        }
        window.addEventListener('load', sanitizeDanglingLabels);
        if (window.MutationObserver) {
            new MutationObserver(sanitizeDanglingLabels).observe(document.documentElement, {
                childList: true,
                subtree: true
            });
        }
    })();
</script>
