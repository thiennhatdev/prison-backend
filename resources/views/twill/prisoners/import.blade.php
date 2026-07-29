<form action="{{ route('twill.prisoners.import.store') }}"
      method="POST"
      enctype="multipart/form-data">

    @csrf

    <input type="file" name="file" required>

    <button type="submit">
        Import
    </button>
</form>