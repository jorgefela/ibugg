
<?php

	include('connect.php');
	require_once('slim.php');

	if(!empty($_POST['process'])){
		$process = $_POST['process'];
	}elseif(!empty($_GET['process'])){
		$process = $_GET['process'];
	}else{
		$process = '';
	}

	$current_date = date('Y-m-d H:i:s');

	switch($process){
		case 'new-password':

			$pass = md5($_POST['password']);
			$c_pass = md5($_POST['confirm-password']);
			$id = $_POST['id'];
			$email = $_POST['email'];

			if($pass === $c_pass){
				$update = $pdo->query('update users set password = "'.$pass.'" where id = "'.$id.'"');
				if($update == true){
					header('Location: '.BASE_URL.'login?e='.base64_encode($email));
				}else{
					header('Location: '.BASE_URL.'forgot-password/restart?id='.base64_encode($id).'&status='.base64_encode('error-update'));
				}
			}else{
				header('Location: '.BASE_URL.'forgot-password/restart?id='.base64_encode($id).'&status='.base64_encode('pass-not-match'));
			}

		break;

		case 'forgot-password':
			$check = $pdo->query('select * from users where email = "'.$_POST['email'].'"');
			$u = $check->fetch(PDO::FETCH_ASSOC);

			/*EMAIL FORGOT PASSWORD*/
			$e = $pdo->query('select * from email_template where id = "1"')->fetch(PDO::FETCH_ASSOC);
			$body = html_entity_decode($e['content']);
			$body = str_replace('#name#', $u['name'], $body);
			$body = str_replace('#email#', $u['email'], $body);
			$body = str_replace('#user_id#', base64_encode($u['id']), $body);
			$body = str_replace('#BASE_URL#', BASE_URL, $body);
			$body = str_replace('#preheader#', $e['preheader'], $body);
			$subject = $e['subject'];

			// echo $body;

			$header = "From: noreply@ibugg.com\r\n"; 
			$header.= "MIME-Version: 1.0\r\n"; 
			$header.= "Content-Type: text/html; charset=ISO-8859-1\r\n"; 
			$header.= "X-Priority: 1\r\n"; 


			if($check->rowCount() > 0){
				$send = mail($u['email'], $subject, $body, $header);

				if($send == true){
					header('Location: ibugg.com/app/forgot-password/?status='.base64_encode('success').'');
				}else{
					header('Location: ibugg.com/app/forgot-password/?status='.base64_encode('error-send').'');
				}
			}else{
				header('Location: ibugg.com/app/forgot-password/?status='.base64_encode('not-match').'');
			}
		break;

		case 'signup':

			$username = $_POST['username'];
			$name = $_POST['name'];
			$lname = $_POST['lname'];
			$email = $_POST['email'];
			$phone = $_POST['phone'];
			$password = md5($_POST['password']);

			$check = $pdo->query('select * from users where email = "'.$email.'" or username = "'.$username.'" or phone = "'.$phone.'"');
			$c = $check->fetch(PDO::FETCH_ASSOC);

			if($check->rowCount() > 0){
				if($c['username'] == $username){
					header('Location: ../signup?sts='.base64_encode('username-exist'));
				}elseif($c['email'] == $email){
					header('Location: ../signup?sts='.base64_encode('email-exist'));
				}elseif($c['phone'] == $phone){
					header('Location: ../signup?sts='.base64_encode('phone-exist'));					
				}
			}else{
				$insert = $pdo->query('insert into users (username, name, lname, email, phone, password) values ("'.$username.'", "'.$name.'", "'.$lname.'", "'.$email.'", "'.$phone.'", "'.$password.'")');
				if($insert == true){
					header('Location: ../login?sts='.base64_encode('account-create'));
				}else{
					header('Location: ../signup?sts='.base64_encode('cookie-error'));
				}
			}

		break;

		case 'login':

			$username = $_POST['username'];
			$user_type = $_POST['user-type'];
			$password = md5($_POST['password']);
			$backurl = $_POST['backurl'];

			if($user_type == 0){
				/*USER*/
				$table = 'users';
			}elseif($user_type == 1){
				/*SUBUSER*/
				$table = 'subusers';
			}else{
				$table = '';
			}

			$check_username = $pdo->query('select * from '.$table.' where username = "'.$username.'" or email = "'.$username.'"');

			if($check_username->rowCount() > 0){
				/*USERNAME OR EMAIL EXIST*/
				$u = $check_username->fetch(PDO::FETCH_ASSOC);
				if($u['password'] === $password){
					/*PASSWORD MATCH WITH ACCOUNT*/
					/*KEEP SESSION ACTIVE*/
				//	if($_POST['keep-session'] == 1){
				//		$expires_cookies = time()+86400*30;
				//	}else{
				//		$expires_cookies = 0;
				//	}
					$expires_cookies = 0;

					/*CREATE COOKIES FOR SESSION*/
					$cookie_session = setcookie('session', true, $expires_cookies, '/');

					$cookie_userid = setcookie('userid', base64_encode($u['id']), $expires_cookies, '/');
					header('Location: ../');
					if($user_type == 1){
						$cookie_userid = setcookie('subuser', base64_encode(true), $expires_cookies, '/');
					}

					//if(!empty($_COOKIE['session']) && !empty($_COOKIE['userid'])){
					//	if(!empty($backurl)){
					//		header('Location: '.$backurl);
					//	}else{
					//		header('Location: ../');
					//	}
			//		}else{
			//			header('Location: ../login?sts='.base64_encode('cookie-error'));
			//		}
				}else{
					/*PASSWORD DOESN'T MATCH WITH ACCOUNT*/
					header('Location: ../login?sts='.base64_encode('pass-not-match'));
				}
			}else{
				/*USERNAME OR EMAIL DOESN'T EXIST*/
				header('Location: ../login?sts='.base64_encode('not-exist'));
		}
		break;

		case 'logout':

			if(!empty($_COOKIE['session'])){
				unset($_COOKIE['session']);
				setcookie('session', null, -1, '/');
			}

			if(!empty($_COOKIE['userid'])){
				unset($_COOKIE['userid']);
				setcookie('userid', null, -1, '/');
			}

			if(!empty($_COOKIE['subuser'])){
				unset($_COOKIE['subuser']);
				setcookie('subuser', null, -1, '/');
			}

			if(empty($_COOKIE['session']) && empty($_COOKIE['userid'])){
				header('Location: ../login');
			}elseif(empty($_COOKIE['session']) && !empty($_COOKIE['userid'])){
				unset($_COOKIE['userid']);
				setcookie('userid', null, -1, '/');
				header('Location: ../php/functions.php?process=logout');
			}elseif(empty($_COOKIE['userid']) && !empty($_COOKIE['session'])){
				unset($_COOKIE['session']);
				setcookie('session', null, -1, '/');
				header('Location: ../php/functions.php?process=logout');
			}
		break;

		case 'new-contact':

			$photo = $_FILES['photo'];
			$name = $_POST['name'];
			$lname = $_POST['lname'];
			$phone = $_POST['phone'];
			$email = $_POST['email'];
			$address_1 = $_POST['address_1'];
			$address_2 = $_POST['address_2'];
			$city = $_POST['city'];
			$country = $_POST['country'];
			$zip_code = $_POST['zip_code'];
			$industry = $_POST['industry'];
			$org_name = $_POST['org_name'];
			$org_url = $_POST['org_url'];
			$org_position = $_POST['org_position'];
			$tag = $_POST['tag'];
			$img = array();

			$check = $pdo->query('select * from contacts where email = "'.$email.'" and userid = "'.$u['id'].'"');

			if($check->rowCount() > 0){
				header('Location: ../contacts/new?status='.base64_encode('email-exist'));
			}else{
				$insert = $pdo->query('insert into contacts (userid, name, lname, phone, email, address_1, address_2, city, country, zip_code, industry, org_name, org_url, org_position, status, created_date, tag) values ("'.$u['id'].'", "'.$name.'", "'.$lname.'", "'.$phone.'", "'.$email.'", "'.$address_1.'", "'.$address_2.'", "'.$city.'", "'.$country.'", "'.$zip_code.'", "'.$industry.'", "'.$org_name.'", "'.$org_url.'", "'.$org_position.'", 1, "'.$current_date.'", "'.$tag.'")');
				$id = $pdo->lastInsertId();

				if($insert == true){
					if($photo['error'] == 0){
						$save_img = save_img($photo, 'contactid-'.$id);

						if($save_img['status'] == true){
							$img['status'] = true;
							$img['name'] = $save_img['name'];
						}else{
							$img['status'] = false;
							$img['name'] = $c['photo'];
						}
					}else{
						$img['status'] = true;
						$img['name'] = $c['photo'];
					}

					$pdo->query('update contacts set photo = "'.$img['name'].'" where id = "'.$id.'"');

					header('Location: ../contacts?id='.$id);
				}else{
					header('Location: ../contacts/new?info='.base64_encode($_POST));
				}
			}
		break;


        case 'new-empresa':

			$photo = $_FILES['photo'];
			$photo2 = $_FILES['photo2'];
			$photo3 = $_FILES['photo3'];
			$name = $_POST['name'];
			$lname = $_POST['lname'];
			$phone = $_POST['phone'];
			$email = $_POST['email'];
			$address_1 = $_POST['address_1'];
			$address_2 = $_POST['address_2'];
			$city = $_POST['city'];
			$country = $_POST['country'];
			$zip_code = $_POST['zip_code'];
			$industry = $_POST['industry'];
			$org_name = $_POST['org_name'];
			$org_url = $_POST['org_url'];
			$org_position = $_POST['org_position'];
			$tag = $_POST['tag'];
			$area=$_POST['area'];
			$state=$_POST['state'];
			$des=$_POST['des'];
			$img = array();




// get posted data, if something is wrong, exit

    $photos = Slim::getImages();
    $photos2 = Slim::getImages('photo2');
    $photos3= Slim::getImages('photo3');



// should always be one file (when posting async)








			//$check = $pdo->query('select * from contacts where email = "'.$email.'" and userid = "'.$u['id'].'"');

			if(0 > 6){
				header('Location: ../empresa/new?status='.base64_encode('email-exist'));
			}else{
				$insert = $pdo->query('insert into contacts (userid, name, lname, phone, email, address_1, address_2, city, country, zip_code, industry, org_name, org_url, org_position, status, created_date, tag, area, state, des ) values ("'.$u['id'].'", "'.$name.'", "'.$lname.'", "'.$phone.'", "'.$email.'", "'.$address_1.'", "'.$address_2.'", "'.$city.'", "'.$country.'", "'.$zip_code.'", "'.$industry.'", "'.$org_name.'", "'.$org_url.'", "'.$org_position.'", 3, "'.$current_date.'", "'.$tag.'","'.$area.'","'.$state.'","'.$des.'")');
				$id = $pdo->lastInsertId();

				if($insert == true){
					if($photo['error'] == 0){
					
					$path=BASE_PATH.'app/images/profile/';	
						
						$photo = $photos[0];
						$photo2 = $photos2[0];
						$photo3 = $photos3[0];
						$ext = explode('.', $photo['name']);
		$ext = $ext[1];
		$nombre='contactid-'.$id.'.jpg';
$file = Slim::saveFile($photo['output']['data'],$nombre ,$path);

						
						
						//$save_img = save_img($photo, 'contactid-'.$id);
						if($photo2['error']==0){
								$nombre2='contactid2-'.$id.'.jpg';
$file2 = Slim::saveFile($photo2['output']['data'],$nombre2 ,$path);
						}
						
						if($photo3['error']==0){
							$nombre3='contactid3-'.$id.'.jpg';
$file3 = Slim::saveFile($photo3['output']['data'],$nombre3 ,$path);
						}
						
					

						if($nombre){
							$img['status'] = true;
							$img['name'] = $nombre;
                             
                             


						}else{
							$img['status'] = false;
							$img['name'] = 'default.jpg';

						}



                        if($nombre2){
					
                             
                             $img2['status'] = true;
							$img2['name'] =$nombre2;



						}else{
						
							$img2['status'] = false;
							$img2['name'] = 'default.jpg';

						}

if($nombre3){
							

							$img3['status'] = true;
							$img3['name'] = $nombre3;


						}else{
						

							$img3['status'] = false;
							$img3['name'] = 'default.jpg';
						}










					}else{
						$img['status'] = true;
						$img['name'] = 'default.jpg';

						$img2['status'] = true;
						$img2['name'] = 'default.jpg';

						$img3['status'] = true;
						$img3['name'] = 'default.jpg';
					}

					$pdo->query('update contacts set photo = "'.$img['name'].'",  photo2 = "'.$img2['name'].'",  photo3 = "'.$img3['name'].'" where id = "'.$id.'"');

					header('Location: ../empresa/?id='.$id);
				}else{
					header('Location: ../empresa/new?info='.base64_encode($_POST));
				}
			}
		break;



		case 'edit-contact':

			$id = $_POST['id'];
			$photo = $_FILES['photo'];
			$name = $_POST['name'];
			$lname = $_POST['lname'];
			$phone = $_POST['phone'];
			$email = $_POST['email'];
			$address_1 = $_POST['address_1'];
			$address_2 = $_POST['address_2'];
			$city = $_POST['city'];
			$country = $_POST['country'];
			$zip_code = $_POST['zip_code'];
			$industry = $_POST['industry'];
			$org_name = $_POST['org_name'];
			$org_url = $_POST['org_url'];
			$org_position = $_POST['org_position'];
			$tag = $_POST['tag'];
			$img = array();

			$contact = $pdo->query('select * from contacts where id = "'.$id.'" and userid = "'.$u['id'].'"');
			$c = $contact->fetch(PDO::FETCH_ASSOC);

			// print_r($photo);

			if($contact->rowCount() > 0){
				if($photo['error'] == 0){
					$save_img = save_img($photo, 'contactid-'.$id);

					if($save_img['status'] == true){
						$img['status'] = true;
						$img['name'] = $save_img['name'];
					}else{
						$img['status'] = false;
						$img['name'] = $c['photo'];
					}
				}else{
					$img['status'] = true;
					$img['name'] = $c['photo'];
				}

				$update = $pdo->query('update contacts set photo = "'.$img['name'].'", name = "'.$name.'", lname = "'.$lname.'", phone = "'.$phone.'", email = "'.$email.'", address_1 = "'.$address_1.'", address_2 = "'.$address_2.'", city = "'.$city.'", country = "'.$country.'", zip_code = "'.$zip_code.'", industry = "'.$industry.'", org_name = "'.$org_name.'", org_url = "'.$org_url.'", org_position = "'.$org_position.'", tag = "'.$tag.'" where id = "'.$id.'"');
				if($update == true){
					header('Location: ../contacts?id='.$id);
				}else{
					header('Location: ../contacts/edit/'.$id.'?status=no-update');
				}
			}
		break;





	case 'edit-contact33':

			$id = $_POST['id'];
			$photo = $_FILES['photo'];
			$photo2 = $_FILES['photo2'];
			$photo3 = $_FILES['photo3'];
			$name = $_POST['name'];
			$lname = $_POST['lname'];
			$phone = $_POST['phone'];
			$email = $_POST['email'];
			$address_1 = $_POST['address_1'];
			$address_2 = $_POST['address_2'];
			$city = $_POST['city'];
			$country = $_POST['country'];
			$zip_code = $_POST['zip_code'];
			$industry = $_POST['industry'];
			$org_name = $_POST['org_name'];
			$org_url = $_POST['org_url'];
			$org_position = $_POST['org_position'];
			$tag = $_POST['tag'];
			$img = array();

			$contact = $pdo->query('select * from contacts where id = "'.$id.'" and userid = "'.$u['id'].'"');
			$c = $contact->fetch(PDO::FETCH_ASSOC);

			// print_r($photo);
$heu = date("YmdHis");
			if($contact->rowCount() > 0){
				if($photo['error'] == 0){
					$save_img = save_img($photo, 'contactid-'.$heu.''.$id);

				

					if($save_img['status'] == true){
						$img['status'] = true;
						$img['name'] = $save_img['name'];
					}else{
						$img['status'] = false;
						$img['name'] = $c['photo'];
					}
				}else{
					$img['status'] = true;
					$img['name'] = $c['photo'];
				}



              if($photo2['error'] == 0){

					$save_img2 = save_img($photo2, 'contactid2-'.$heu.''.$id);
					if($save_img2['status'] == true){
						$img2['status'] = true;
						$img2['name'] = $save_img2['name'];
					}else{
						$img2['status'] = false;
						$img2['name'] = $c['photo2'];
					}
			}else{
					$img2['status'] = true;
					$img2['name'] = $c['photo2'];
				}
				

            if($photo3['error'] == 0){


               	$save_img3 = save_img($photo3, 'contactid3-'.$heu.''.$id);

					if($save_img3['status'] == true){
						$img3['status'] = true;
						$img3['name'] = $save_img3['name'];
					}else{
						$img3['status'] = false;
						$img3['name'] = $c['photo3'];
					}
				}else{
					$img3['status'] = true;
					$img3['name'] = $c['photo3'];
				}

				$update = $pdo->query('update contacts set photo= "'.$img['name'].'", photo2 = "'.$img2['name'].'", photo3 = "'.$img3['name'].'", name = "'.$name.'", lname = "'.$lname.'", phone = "'.$phone.'", email = "'.$email.'", address_1 = "'.$address_1.'", address_2 = "'.$address_2.'", city = "'.$city.'", country = "'.$country.'", zip_code = "'.$zip_code.'", industry = "'.$industry.'", org_name = "'.$org_name.'", org_url = "'.$org_url.'", org_position = "'.$org_position.'", tag = "'.$tag.'" where id = "'.$id.'"');
				if($update == true){
					header('Location: ../empresa?id='.$id);
				}else{
					header('Location: ../empresa/edit/'.$id.'?status=no-update');
				}
			}
		break;




case 'edit-usu':

			$id = $_POST['id'];
			$photo = $_FILES['photo'];
			$name = $_POST['name'];
			$lname = $_POST['lname'];
			$phone = $_POST['phone'];
			$email = $_POST['email'];
			$address_1 = $_POST['address_1'];
			$address_2 = $_POST['address_2'];
			$city = $_POST['city'];
			$country = $_POST['country'];
			$zip_code = $_POST['zip_code'];
			$industry = $_POST['industry'];
			$org_name = $_POST['org_name'];
			$org_url = $_POST['org_url'];
			$org_position = $_POST['org_position'];
			$tag = $_POST['tag'];
			$img = array();

			$contact = $pdo->query('select * from users where id = "'.$id.'" ');
			$c = $contact->fetch(PDO::FETCH_ASSOC);

			// print_r($photo);

			if($contact->rowCount() > 0){
				if($photo['error'] == 0){
					$save_img = save_img($photo, 'contactid-'.$id);

					if($save_img['status'] == true){
						$img['status'] = true;
						$img['name'] = $save_img['name'];
					}else{
						$img['status'] = false;
						$img['name'] = $c['photo'];
					}
				}else{
					$img['status'] = true;
					$img['name'] = $c['photo'];
				}

				$update = $pdo->query('update users set photo = "'.$img['name'].'", name = "'.$name.'", lname = "'.$lname.'", phone = "'.$phone.'", email = "'.$email.'", address_1 = "'.$address_1.'", address_2 = "'.$address_2.'", city = "'.$city.'", country = "'.$country.'", zip_code = "'.$zip_code.'", industry = "'.$industry.'", org_name = "'.$org_name.'", org_url = "'.$org_url.'", org_position = "'.$org_position.'" where id = "'.$id.'"');
				if($update == true){
					header('Location: ../perfil_usu/profile/?id='.$id);
				}else{
					header('Location: ../perfil_usu/profile/?id='.$id.'?status=no-update');
				}
			}
		break;


		case 'edit-user':

			$id = $_POST['id'];
			$username = $_POST['username'];
			$photo = $_FILES['photo'];
			$name = $_POST['name'];
			$lname = $_POST['lname'];
			$phone = $_POST['phone'];
			$email = $_POST['email'];
			$address_1 = $_POST['address_1'];
			$address_2 = $_POST['address_2'];
			$city = $_POST['city'];
			$country = $_POST['country'];
			$zip_code = $_POST['zip_code'];
			$industry = $_POST['industry'];
			$org_name = $_POST['org_name'];
			$org_url = $_POST['org_url'];
			$org_position = $_POST['org_position'];
			$tag = $_POST['tag'];
			$password = $_POST['password'];
			$confirm_password = $_POST['confirm_password'];
			$img = array();

			$contact = $pdo->query('select * from contacts where id = "'.$id.'" and userid = "'.$u['id'].'"');
			$c = $contact->fetch(PDO::FETCH_ASSOC);

			if(!empty($password)){
				if($password != $confirm_password){
					header('Location: ../perfil_usu/edit/?id='.$id.'?status='.base64_encode('not-same-password'));
					return false;
				}else{
					if($pdo->query('update users set password = "'.md5($password).'" where id = "'.$id.'"')){
						$update_pass = true;
					}
				}
			}else{
				$update_pass = true;
			}

			if($c['username'] != $username){
				$user = $pdo->query('select * form users where username = "'.$username.'" and id != "'.$id.'"')->rowCount();
				if($user > 0){
					header('Location: ../perfil_usu/edit/?id='.$id.'/'.base64_encode('username-exist'));
					return false;
				}else{
					if($pdo->query('update users set username = "'.$username.'" where id = "'.$id.'"')){
						$update_user = true;
					}
				}
			}else{
				$update_user = true;
			}

			if($contact->rowCount() > 0){
				if($photo['error'] == 0){
					$save_img = save_img($photo, 'userid-'.$id);

					if($save_img['status'] == true){
						$img['status'] = true;
						$img['name'] = $save_img['name'];
					}else{
						$img['status'] = false;
						$img['name'] = $c['photo'];
					}
				}else{
					$img['status'] = true;
					$img['name'] = $c['photo'];
				}

				$update = $pdo->query('update users set photo = "'.$img['name'].'", name = "'.$name.'", lname = "'.$lname.'", phone = "'.$phone.'", email = "'.$email.'", address_1 = "'.$address_1.'", address_2 = "'.$address_2.'", city = "'.$city.'", country = "'.$country.'", zip_code = "'.$zip_code.'", industry = "'.$industry.'", org_name = "'.$org_name.'", org_url = "'.$org_url.'", org_position = "'.$org_position.'" where id = "'.$id.'"');
				if($update == true){
					header('Location: ../perfil_usu/?id='.$id);
				}else{
					header('Location: ../perfil_usu/edit/?id='.$id.'?status=no-update');
				}
			}
		break;

		case 'change-favorite':

			$update = $pdo->query('update contacts set favorite = "'.$_GET['fav'].'" where id = "'.$_GET['id'].'"');
			if($update == true){
				header('Location: ../contacts?id='.$_GET['id']);
			}else{
				header('Location: ../contacts?status='.base64_encode('favorite-error'));
			}
		break;

		case 'delete-users':
			$update = $pdo->query('update users set status = "2" where id = "'.$_GET['id'].'"');
			if($update == true){
				header('Location: ../users?id='.$_GET['id']);
			}else{
				header('Location: ../users?status='.base64_encode('delete-error'));
			}
		break;

		case 'delete-contacts':
			$update = $pdo->query('update contacts set status = "2" where id = "'.$_GET['id'].'"');
			if($update == true){
				header('Location: ../contacts?id='.$_GET['id']);
			}else{
				header('Location: ../contacts?status='.base64_encode('delete-error'));
			}
		break;

		case 'delete-ads':
			$update = $pdo->query('update contacts set status = "2" where id = "'.$_GET['id'].'"');
			if($update == true){
				header('Location: ../empresa');
			}else{
				header('Location: ../empresa?status='.base64_encode('delete-error'));
			}
		break;
	}

	function save_img2($photo, $name){
		// global BASE_DIR;
		// print_r($photo);

		$tmp = $photo['tmp_name'];
		$return = array();
		list($w, $h) = getimagesize($tmp);

		$percent = $h/$w;
		$new_width = 500;
		$new_height = $new_width * $percent;

		$ext = explode('.', $photo['name']);
		$ext = $ext[1];
         $str = str_replace("/", "", $name);

		$dir = BASE_PATH.'app/images/profile/'.$str.'.'.$ext;

		$thumb = imagecreatetruecolor($new_width, $new_height);
		if($ext == 'jpg' || $ext == 'jpeg'){
			$origen = imagecreatefromjpeg($tmp);
			imagecopyresized($thumb, $origen, 0, 0, 0, 0, $new_width, $new_height, $w, $h);
			$move = imagejpeg($thumb, $dir);
		}elseif($ext == 'png'){
			$origen = imagecreatefrompng($tmp);
			imagealphablending($thumb, false);
			imagesavealpha($thumb, true);
			imagecopyresized($thumb, $origen, 0, 0, 0, 0, $new_width, $new_height, $w, $h);
			$move = imagepng($thumb, $dir);
		}



		if($move == true){
			$return['status'] = true;
			$return['name'] = $str.'.'.$ext;
		}else{
			$return['status'] = false;
		}

		return $return;
	}
	
	
	
	
	
		function save_img($photo, $name){
		// global BASE_DIR;
		// print_r($photo);

		$tmp = $photo['tmp_name'];
		$return = array();
		list($w, $h) = getimagesize($tmp);

		$percent = $h/$w;
		$new_width = 500;
		$new_height = $new_width * $percent;

		$ext = explode('.', $photo['name']);
		$ext = $ext[1];
         $str = str_replace("/", "", $name);

		$dir = BASE_PATH.'app/images/profile/'.$str.'.'.$ext;

		$thumb = imagecreatetruecolor($new_width, $new_height);
		if($ext == 'jpg' || $ext == 'jpeg'){
			$origen = imagecreatefromjpeg($tmp);
			imagecopyresized($thumb, $origen, 0, 0, 0, 0, $new_width, $new_height, $w, $h);
			$move = imagejpeg($thumb, $dir);
		}elseif($ext == 'png'){
			$origen = imagecreatefrompng($tmp);
			imagealphablending($thumb, false);
			imagesavealpha($thumb, true);
			imagecopyresized($thumb, $origen, 0, 0, 0, 0, $new_width, $new_height, $w, $h);
			$move = imagepng($thumb, $dir);
		}



		if($move == true){
			$return['status'] = true;
			$return['name'] = $str.'.'.$ext;
		}else{
			$return['status'] = false;
		}

		return $return;
	}
	
	
	
// funcion vieja
	function check_session($login = false){
		$backurl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

		if(!empty($_COOKIE['session']) && !empty($_COOKIE['userid'])){
			if($login == true){
				header('Location: ../');	
			}
		}else{
			if($login == false){

				header('Location: ../login?backurl='.base64_encode($backurl));
				
			
			}
		}
	}


// funcion de check
	function check_session3($login = false){
		
		if($_COOKIE['session']){
			
				//header('Location: ../');
			
		}else{
			
				header('Location: ../login');
			
		}
	}



//hasta aqui


	function head_line($extras){
		$title = (!empty($extras['title'])) ? $extras['title'] : 'Iglesia Cristiana en Monterrey | Dunamis Ministerio Internacional';
		$description = (!empty($extras['description'])) ? $extras['description'] : '';
		$keywords = (!empty($extras['keywords'])) ? $extras['keywords'] : '';
		$url = (!empty($extras['url'])) ? $extras['url'] : '';
		$favicon = (!empty($extras['favicon'])) ? $extras['favicon'] : '';
		$share_img = (!empty($extras['share_img'])) ? $extras['share_img'] : '';
		$page = (!empty($extras['page'])) ? $extras['page'] : '';
		$js = (!empty($extras['includes']['js'])) ? $extras['includes']['js'] : '';
		$css = (!empty($extras['includes']['css'])) ? $extras['includes']['css'] : '';

		echo '
			<!DOCTYPE html>
			<html lang="en">
			<head>
				<base href="'.BASE_URL.'">
				<meta charset="UTF-8">
				<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
				<title>'.$title.'</title>
				<meta name="description" content="'.$description.'">
				<meta name="keywords" content="'.$keywords.'">

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css">

<!-- jQuery library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<!-- Popper JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>

<!-- Latest compiled JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js"></script>


<link href="php/easy/dist/easy-autocomplete.min.css" rel="stylesheet" type="text/css">
		<link href="php/easy/dist/easy-autocomplete.themes.min.css" rel="stylesheet" type="text/css">
	
	
		<script>
		
		$(window).on("load", function () {
      setTimeout(function () {
    $(".loader-page").css({visibility:"hidden",opacity:"0"})
  }, 2000);
     
});
		</script>
		
		<style>
		.loader-page {
    position: fixed;
    z-index: 25000;
    background: rgb(255, 255, 255);
    left: 0px;
    top: 0px;
    height: 100%;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition:all .3s ease;
  }
  .loader-page::before {
    content: "";
    position: absolute;
    border: 2px solid rgb(50, 150, 176);
    width: 60px;
    height: 60px;
    border-radius: 50%;
    box-sizing: border-box;
    border-left: 2px solid rgba(50, 150, 176,0);
    border-top: 2px solid rgba(50, 150, 176,0);
    animation: rotarload 1s linear infinite;
    transform: rotate(0deg);
  }
  @keyframes rotarload {
      0%   {transform: rotate(0deg)}
      100% {transform: rotate(360deg)}
  }
  .loader-page::after {
    content: "";
    position: absolute;
    border: 2px solid rgba(50, 150, 176,.5);
    width: 60px;
    height: 60px;
    border-radius: 50%;
    box-sizing: border-box;
    border-left: 2px solid rgba(50, 150, 176, 0);
    border-top: 2px solid rgba(50, 150, 176, 0);
    animation: rotarload 1s ease-out infinite;
    transform: rotate(0deg);
  }
		</style>
		
		


				<!-- CSS -->
				<link rel="stylesheet" href="css/style.css">
				<link rel="stylesheet" href="css/font-awesome.css">
				<link rel="stylesheet" href="css/font.css">
				'.$css.'
				<!-- JS -->
				<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
				<script src="js/script.js"></script>
				'.$js.'
				<!-- FAVICON -->
				<link rel="apple-touch-icon" sizes="57x57" href="images/favicon/apple-touch-icon-57x57.png">
				<link rel="apple-touch-icon" sizes="60x60" href="images/favicon/apple-touch-icon-60x60.png">
				<link rel="apple-touch-icon" sizes="72x72" href="images/favicon/apple-touch-icon-72x72.png">
				<link rel="apple-touch-icon" sizes="76x76" href="images/favicon/apple-touch-icon-76x76.png">
				<link rel="apple-touch-icon" sizes="114x114" href="images/favicon/apple-touch-icon-114x114.png">
				<link rel="apple-touch-icon" sizes="120x120" href="images/favicon/apple-touch-icon-120x120.png">
				<link rel="apple-touch-icon" sizes="144x144" href="images/favicon/apple-touch-icon-144x144.png">
				<link rel="apple-touch-icon" sizes="152x152" href="images/favicon/apple-touch-icon-152x152.png">
				<link rel="apple-touch-icon" sizes="180x180" href="images/favicon/apple-touch-icon-180x180.png">
				<link rel="icon" type="image/png" sizes="32x32" href="images/favicon/favicon-32x32.png">
				<link rel="icon" type="image/png" sizes="192x192" href="images/favicon/android-chrome-192x192.png">
				<link rel="icon" type="image/png" sizes="16x16" href="images/favicon/favicon-16x16.png">
				<link rel="manifest" href="images/favicon/manifest.json">
				<link rel="mask-icon" href="images/favicon/safari-pinned-tab.svg" color="#0000ff">
				<meta name="msapplication-TileColor" content="#0000ff">
				<meta name="msapplication-TileImage" content="images/favicon/mstile-144x144.png">
				<meta name="theme-color" content="#ffffff">
			</head>
		';
	}

	function header_line(){
		global $u;

		if($u['photo'] != ''){
			$photo = $u['photo'];
		}else{
			$photo = 'default.jpg';
		}

		if($u['city'] != '' && $u['country'] != ''){
			$place = $u['city'].', '.$u['country'];
		}elseif($u['city'] != '' && $u['country'] == ''){
			$place = $u['city'];
		}elseif($u['country'] != '' && $u['city'] == ''){
			$place = $u['country'];
		}else{
			$place = '('.$u['username'].')';
		}

		echo '
			<body>
				<div id="body-app">';
				// if($support != false){
					if(!empty($u)){
						echo '
						<div class="menu-app">
							<div class="menu-app-account">
								<div class="menu-app-account-img">
									<div style="background-image: url(images/profile/'.$photo.');">
									</div>
								</div>
								<div class="menu-app-account-info">
									<a href="perfil_usu/profile/?id='.$u['id'].'" style="color:#fff"><h1>'.$u['name'].' '.$u['lname'].'</h1></a>
									<p>'.$place.'</p>
								</div>
							</div>
							<div class="menu-app-list">
								<ul>
									<li><a href=""><i class="fa fa-home" aria-hidden="true"></i> <span>Dashboard</span></a></li>
									<li><a href="contacts"><i class="fa fa-user" aria-hidden="true"></i> <span>Contacts</span></a></li>
									<li><a href="favorites"><i class="fa fa-heart" aria-hidden="true"></i> <span>Favorite</span></a></li>
                                    <li><a href="empresa"><i class="fa fa-address-card" aria-hidden="true"></i> <span>Advertisements</span></a></li>
                                      <li><a href="public"><i class="fa fa-address-book" aria-hidden="true"></i> <span>Directory</span></a></li>
									';
									if($_COOKIE['subuser'] != true || $u['subuser'] != 1){
										echo '<!--<li><a href="users"><i class="fa fa-users" aria-hidden="true"></i> <span>Subusers</span></a></li>-->';
									}
									echo '
									<li><a href="support"><i class="fa fa-life-ring" aria-hidden="true"></i> <span>Support</span></a></li>
									<li><a href="php/functions.php?process=logout"><i class="fa fa-sign-out" aria-hidden="true"></i> <span>Logout</span></a></li>
								</ul>
							</div>
						</div>';
					}
				// }
					echo '
					<div class="content-app">
						<header>
							<div class="header-item-menu header-item">
								<a href="#" id="menu-btn"><span><i class="fa fa-bars" aria-hidden="true"></i></span></a>
							</div>
							<div class="header-item-logo header-item">
								<img src="images/white-logo-horz.png" alt="Ibugg">
							</div>
							<div class="header-item-search header-item"></div>
						</header>
		';
	}

	function footer_line(){
		echo '

				</div>
			</div>


<div class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      ...
    </div>
  </div>
</div>



<!-- Modal -->
<div class="modal fade" id="city" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">City</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" >
      <input type="text" class="form-control" id="ci" placeholder="Cyty or state">  
      </div>
      <div class="modal-footer" id="col">
     
      </div>
    </div>
  </div>
</div>









		</body>
		</html>
		';
	}

?>
