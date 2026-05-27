{{--
    Pass-through wrapper. The split-screen guest layout already handles the
    chrome (header, marketing column, footer) so the form just needs a
    container. The `logo` slot is intentionally dropped — the layout's
    header already renders the wordmark.
--}}
<div class="space-y-1">
    {{ $slot }}
</div>
