{{--
    Umami site analytics. Loaded async via defer so it never blocks
    paint. Skipped on local + testing envs so dev usage doesn't
    pollute the production dashboard and tests don't make outbound
    requests. Production + any other env (staging, demo) emit
    normally.
--}}
@if (! app()->environment(['local', 'testing']))
<script defer src="https://cloud.umami.is/script.js" data-website-id="090b0221-507b-4776-b8a5-de67b1107a5b"></script>
@endif
