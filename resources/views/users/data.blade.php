<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <title>CRUD api - Data only</title>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">CRUD API</a>
  </div>
</nav>
<table class="table table-dark table-striped">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Name</th>
      <th scope="col">Email</th>
      <th scope="col">Tasklist</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($users as $user)
    <tr>
      <th scope="row">{{ $user->id }}</th>
      <td>{{ $user->name }}</td>
      <td>{{ $user->email }}</td>
      <td>
      @forelse ($user->tasks as $task)
        <div>{{ $task->title }}</div>
      @empty
        <div>No tasks found.</div>
      @endforelse
    </td>
    </tr>
    @empty
    <tr>
      <td colspan="4">No data found.</td>
    </tr>
    @endforelse 
  </tbody>
</table>
</body>
</html>