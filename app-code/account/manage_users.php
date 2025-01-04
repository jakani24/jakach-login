<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <?php
      include "../assets/components.php";
    ?>
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">User Management</h1>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody id="userTableBody">
        <!-- User rows will be dynamically inserted here -->
        </tbody>
    </table>
</div>

<script>
    async function fetchUsers() {
        try {
            const response = await fetch('/api/manage/fetch_users.php');
            const data = await response.json();

            if (data.success) {
                const userTableBody = document.getElementById('userTableBody');
                userTableBody.innerHTML = ''; // Clear existing rows

                data.data.forEach(user => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${user.id}</td>
                        <td>${user.username}</td>
                        <td>
                            <button class="btn btn-danger btn-sm" onclick="deleteUser(${user.id})">Delete</button>
                        </td>
                    `;
                    userTableBody.appendChild(row);
                });
            } else {
                console.error(data.message);
            }
        } catch (error) {
            console.error('Error fetching users:', error);
        }
    }

    async function deleteUser(userId) {
        if (!confirm('Are you sure you want to delete this user?')) return;

        try {
            const response = await fetch(`/api/manage/delete_user.php?id=${userId}`, { method: 'DELETE' });
            const data = await response.json();

            if (data.success) {
                alert('User deleted successfully!');
                fetchUsers(); // Refresh the user list
            } else {
                alert(`Error: ${data.message}`);
            }
        } catch (error) {
            console.error('Error deleting user:', error);
        }
    }

    // Fetch users on page load
    fetchUsers();
</script>
</body>
</html>
