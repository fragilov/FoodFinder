<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php'; require_once __DIR__ . '/../../app/services/upload.php'; require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; } csrf_check($_POST['csrf'] ?? null);
$id=filter_var($_POST['id'] ?? 0,FILTER_VALIDATE_INT,['options'=>['min_range'=>0]]) ?: 0;
$old=$_POST; unset($old['csrf'],$old['id']); $_SESSION['form_old']=$old;
try {
  $name=trim((string)($_POST['name']??'')); $address=trim((string)($_POST['address']??'')); $category=filter_var($_POST['category_id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
  $lat=filter_var($_POST['latitude']??null,FILTER_VALIDATE_FLOAT); $lng=filter_var($_POST['longitude']??null,FILTER_VALIDATE_FLOAT); $min=filter_var($_POST['price_min']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>0]]); $max=filter_var($_POST['price_max']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>0]]);
  $level=filter_var($_POST['price_level']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>3]]); $rating=filter_var($_POST['rating']??null,FILTER_VALIDATE_FLOAT); $ratingCount=filter_var($_POST['rating_count']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>0]]);
  if(mb_strlen($name)<2||mb_strlen($name)>150||$address===''||mb_strlen($address)>255||!$category||$lat===false||$lng===false||$lat < -90||$lat>90||$lng < -180||$lng>180||$min===false||$max===false||$min>$max||!$level||$rating===false||$rating<0||$rating>5||$ratingCount===false) throw new InvalidArgumentException('Hãy kiểm tra lại các trường bắt buộc, tọa độ, giá và điểm đánh giá.');
  $pdo=db(); $check=$pdo->prepare('SELECT 1 FROM categories WHERE id=:id');$check->execute([':id'=>$category]);if(!$check->fetchColumn())throw new InvalidArgumentException('Danh mục không tồn tại.');
  $image=store_restaurant_image($_FILES['image']??[]); $fields=['category_id'=>$category,'name'=>$name,'address'=>$address,'phone'=>trim((string)($_POST['phone']??''))?:null,'latitude'=>$lat,'longitude'=>$lng,'price_min'=>$min,'price_max'=>$max,'price_level'=>$level,'rating'=>$rating,'rating_count'=>$ratingCount,'open_time'=>($_POST['open_time']??'')?:null,'close_time'=>($_POST['close_time']??'')?:null,'is_24h'=>isset($_POST['is_24h'])?1:0,'note'=>trim((string)($_POST['note']??''))?:null];if($image)$fields['image_url']=$image;
  if($id){$sets=implode(',',array_map(static fn($key)=>"$key=:$key",array_keys($fields)));$fields['id']=$id;$pdo->prepare("UPDATE restaurants SET $sets WHERE id=:id")->execute($fields);$message='Đã cập nhật quán ăn.';}else{$columns=implode(',',array_keys($fields));$values=implode(',',array_map(static fn($key)=>":$key",array_keys($fields)));$pdo->prepare("INSERT INTO restaurants ($columns) VALUES ($values)")->execute($fields);$message='Đã thêm quán ăn.';}
  unset($_SESSION['form_old']);admin_flash($message);header('Location: index.php');
}catch(Throwable $e){admin_flash($e instanceof InvalidArgumentException?$e->getMessage():'Không thể lưu quán ăn.','error');header('Location: restaurant_form.php'.($id?'?id='.$id:''));}

