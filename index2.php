<?php
    include('dbcon.php'); 
    include('check.php');

    if(is_login()){

        if ($_SESSION['user_id'] == 'admin' && $_SESSION['is_admin']==1)
            header("Location: admin.php");
        else 
            header("Location: welcome.php");
    }
?>

<!DOCTYPE html>
<html>
<head>
	<title>ログインEX</title>
	<link rel="stylesheet" href="bootstrap/css/bootstrap1.min.css">
</head>


<body>

<div class="container">

	<h2 align="center">ログイン</h2><hr>
	<form class="form-horizontal" method="POST">
		<div class="form-group" style="padding: 10px 10px 10px 10px;">
			<label for="user_name">ID:</label>
			<input type="text" name="user_name"  class="form-control" id="inputID" placeholder="IDを入力してください。" 
				required autocomplete="off" readonly onfocus="this.removeAttribute('readonly');" />
		</div>
		<div class="form-group" style="padding: 10px 10px 10px 10px;">
			<label for="user_password">PW:</label>
			<input type="password" name="user_password" class="form-control" id="inputPassword" placeholder="PWを入力してください。" 
				required  autocomplete="off" readonly onfocus="this.removeAttribute('readonly');" />
		</div>
		<div class="checkbox">
			<label><input type="checkbox"> ID保存</label>
		</div>
		</br>
		<div class="from-group" style="padding: 10px 10px 10px 10px;" >
			<button type="submit" name="login" class="btn btn-success">ログイン</button>
			<a class="btn btn-success" href="registration.php" style="margin-left: 50px">
			<span class="glyphicon glyphicon-user"></span>&nbsp;登録
			</a>
		</div>
		</br>
	</form>
</div>
</body>
</html>


<?php

    $login_ok = false;

    if ( ($_SERVER['REQUEST_METHOD'] == 'POST') and isset($_POST['login']) )
    {
		$username=$_POST['user_name'];  
		$userpassowrd=$_POST['user_password'];  

		if(empty($username)){
			$errMSG = "IDを入力してください。";
		}else if(empty($userpassowrd)){
			$errMSG = "PWを入力してください。";
		}else{
			

			try { 

				$stmt = $con->prepare('select * from users where username=:username');

				$stmt->bindParam(':username', $username);
				$stmt->execute();
			   
			} catch(PDOException $e) {
				die("Database error. " . $e->getMessage()); 
			}

			$row = $stmt->fetch();  
			$salt = $row['salt'];
			$password = $row['password'];
			
			$decrypted_password = decrypt(base64_decode($password), $salt);

			if ( $userpassowrd == $decrypted_password) {
				$login_ok = true;
			}
		}

		
		if(isset($errMSG)) 
			echo "<script>alert('$errMSG')</script>";
		

        if ($login_ok){

            if ($row['activate']==0)
				echo "<script>alert('$username アカウントが無効化状態です。')</script>";
            else{
					session_regenerate_id();
					$_SESSION['user_id'] = $username;
					$_SESSION['is_admin'] = $row['is_admin'];

					if ($username=='admin' && $row['is_admin']==1 )
						header('location:admin.php');
					else
						header('location:welcome.php');
					session_write_close();
			}
		}
		else{
			echo "<script>alert('$username 認証エラー')</script>";
		}
	}
?>