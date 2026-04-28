<form  action="{{ route('morosos.subir-excel') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <input type="file" name="archivo" accept=".xlsx,.xls,.csv" required>

    <button type="submit">
        Subir Excel
    </button>
</form>
