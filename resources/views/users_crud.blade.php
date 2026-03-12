<!DOCTYPE html>
<html>
<head>
    <title>Quản lý Users - Backend</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2>Danh sách Users (Backend)</h2>
    
    <form action="/users" method="POST" class="mb-4">
        @csrf
        <div class="input-group">
            <input type="text" name="name" class="form-control" placeholder="Nhập tên user mới" required>
            <button type="submit" class="btn btn-primary">Thêm mới</button>
        </div>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>
                    <form action="/users/{{ $user->id }}" method="POST" style="display:inline;">
                        @csrf @method('PUT')
                        <input type="text" name="name" value="{{ $user->name }}" class="form-control d-inline w-50">
                        <button type="submit" class="btn btn-sm btn-success">Sửa</button>
                    </form>
                </td>
                <td>
                    <form action="/users/{{ $user->id }}" method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Xóa?')">Xóa</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>