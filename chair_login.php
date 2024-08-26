<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IDIS Authentication</title>
    <link rel="stylesheet" href="style2.css">
</head>
<body>
    <div class="main">
        <header>
            <h2>INSTRUCTIONAL DELIVERY IMPLEMENTATION SYSTEM</h2>
        </header>
        
        <div class="login-wrapper">
            <div class="logo"></div>
            <h2>LOGIN AS PROGRAM CHAIR</h2>
            <form action="login.php" method="post">
                <div class="input-field">
                    <input type="number" name="id" id="program-chair-id" required placeholder=" ">
                    <label for="program-chair-id">Instructor ID</label>
                </div>

                <div class="input-field">
                    <input type="password" name="password" id="password" required placeholder=" ">
                    <label for="password">Password</label>
                </div>

                <input type="hidden" name="user_type" value="program_chair">
                <button type="submit" class="login-btn">LOGIN</button>
                <h4 class="return-home"><a href="login.php" style="color: #fff;">Click here to return home</a></h4>
            </form>
        </div>
        
        <footer>
            <h5>All rights reserved 2024<br>
            © IDIS SYSTEM</h5>
        </footer>
    </div>
</body>
</html>
