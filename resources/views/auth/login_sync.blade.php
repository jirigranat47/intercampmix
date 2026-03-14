<!DOCTYPE html>
<html>
<head>
    <title>Authenticating...</title>
</head>
<body>
    <script>
        const token = "{{ $token }}";
        if (token) {
            localStorage.setItem('access_token', token);
            // Sync to cookie for PHP access
            document.cookie = "access_token=" + token + "; path=/; max-age=" + (60 * 60 * 24 * 365);
        }
        window.location.href = "{{ url('/') }}";
    </script>
</body>
</html>
