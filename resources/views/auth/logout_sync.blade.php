<!DOCTYPE html>
<html>
<head>
    <title>Logging out...</title>
</head>
<body>
    <script>
        localStorage.removeItem('access_token');
        document.cookie = "access_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC;";
        window.location.href = "{{ url('/') }}";
    </script>
</body>
</html>
