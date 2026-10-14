<?php
// Dummy data for testing
$user_id = "84";
$username = "Framez";
$email = "vchernev91@abv.bg";
$usercode = "osdlnlvjsnsdnlnvsdsdk";
$is_bot = false;
$is_active = true;
$created_at = '2024-01-01 12:00:00';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile – <?php echo htmlspecialchars($username); ?></title>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #fff; /* white canvas for entire page */
        margin: 0;
        padding: 2rem;
		padding-top: 5rem;
        color: #333;
    }
    .profile-container {
        max-width: 480px;
        margin: 0 auto;
        text-align: center;
    }
    .avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background-color: #ddd;
        display: inline-block;
        margin-bottom: 1rem;
        object-fit: cover;
    }
    h1 { margin: 0.5rem 0; font-size: 1.8rem; }
    p { color: #555; margin: 0.3rem 0; font-size: 1rem; }
    .bio { margin-top: 1rem; font-size: 0.95rem; color: #666; }
</style>
</head>
<body>

<div class="profile-container">
    <!-- Avatar -->
	<img src="avatars/snail.jpg" alt="Avatar" class="avatar">

    <!-- Username -->
    <h1><?php echo htmlspecialchars($username); ?></h1>

    <!-- Basic info -->
    <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
    <p><strong>Usercode:</strong> <?php echo htmlspecialchars($usercode); ?></p>
    <p><strong>Joined:</strong> <?php echo htmlspecialchars(date("F j, Y", strtotime($created_at))); ?></p>
    <p><strong>Status:</strong> <?php echo $is_active ? 'Active' : 'Inactive'; ?><?php echo $is_bot ? ' (Bot)' : ''; ?></p>

    <!-- Basic bio -->
    <div class="bio">
        This is your profile. You can update your avatar and bio here in the future.
    </div>
</div>

</body>
</html>