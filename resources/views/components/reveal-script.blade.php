{{-- Scroll-reveal + count-up for marketing pages. No dependencies; safe on the
     standalone (no-Alpine) pages. Reveals [data-reveal] elements and animates
     [data-countup] numbers when they enter the viewport. --}}
<script>
    (function () {
        var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        // Count-up numbers.
        function countUp(el) {
            var target = parseFloat(el.getAttribute('data-countup'));
            var decimals = (el.getAttribute('data-decimals') | 0);
            var suffix = el.getAttribute('data-suffix') || '';
            var prefix = el.getAttribute('data-prefix') || '';
            if (reduce || isNaN(target)) {
                el.textContent = prefix + target.toFixed(decimals) + suffix;
                return;
            }
            var start = null, dur = 1100;
            function step(ts) {
                if (start === null) start = ts;
                var p = Math.min((ts - start) / dur, 1);
                var eased = 1 - Math.pow(1 - p, 3);
                el.textContent = prefix + (target * eased).toFixed(decimals) + suffix;
                if (p < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        }

        if (! ('IntersectionObserver' in window)) {
            document.querySelectorAll('[data-reveal]').forEach(function (el) { el.classList.add('is-revealed'); });
            document.querySelectorAll('[data-countup]').forEach(countUp);
            return;
        }

        var obs = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (! entry.isIntersecting) return;
                var el = entry.target;
                el.classList.add('is-revealed');
                if (el.hasAttribute('data-countup')) countUp(el);
                obs.unobserve(el);
            });
        }, { rootMargin: '0px 0px -10% 0px', threshold: 0.1 });

        document.querySelectorAll('[data-reveal], [data-countup]').forEach(function (el) { obs.observe(el); });
    })();
</script>
