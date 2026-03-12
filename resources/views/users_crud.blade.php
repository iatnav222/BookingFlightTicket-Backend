<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản trị Hệ thống - Đặt vé máy bay</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5 p-4 bg-white shadow-sm rounded">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary">Quản lý người dùng</h2>
            <a href="{{ url('/users') }}" target="_blank" class="btn btn-outline-info btn-sm">Xem JSON API</a>
        </div>
        
        <form action="{{ url('/users') }}" method="POST" class="mb-4">
            @csrf
            <div class="input-group shadow-sm">
                <input type="text" name="name" class="form-control" placeholder="Nhập họ tên người dùng..." required>
                <button type="submit" class="btn btn-primary px-4">Thêm mới</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 10%">ID</th>
                        <th style="width: 60%">Họ và Tên</th>
                        <th style="width: 30%" class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td><strong>#{{ $user->id }}</strong></td>
                        <td>
                            <form action="{{ url('/users/'.$user->id) }}" method="POST">
                                @csrf @method('PUT')
                                <div class="input-group input-group-sm">
                                    <input type="text" name="name" value="{{ $user->name }}" class="form-control">
                                    <button type="submit" class="btn btn-success">Sửa</button>
                                </div>
                            </form>
                        </td>
                        <td class="text-center">
                            <form action="{{ url('/users/'.$user->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này?')">
                                    Xóa
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">Chưa có người dùng nào trong hệ thống.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>