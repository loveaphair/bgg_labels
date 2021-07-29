<!DOCTYPE html>
<html>
	<head>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	</head>
	<style>
		.collection {
			page-break-after:always;
			font-size:75%;
		}
		img {
			max-width:90px;
		}
		.venmo-img {
			max-width:70px;
		}
		.img-col {
			max-width:90px;
		}
		.seller-info p {
			line-height:12px;
			margin-bottom:0.4rem;
		}
		.set-min {
			min-height:130px;
		}
		.container {
			max-width:600px;
		}
	</style>
	<script type="text/javascript">
		$(function() {
			$('#reload').on('click', function() {
				location.reload();
			});
		});
	</script>
	<body>
	<div class="container">

<?php
// Show the form to get their data
if(empty($_GET['user_name'])) { ?>
		<div class="row mt-4">
			<div class="col">
				<h2>Board Game Label Generator</h2>
				<h4>Instructions:</h4>
				<p>Go to your <a href="https://boardgamegeek.com" target="_blank">boardgamegeek.com</a> account and view your <strong>collection</strong>.</p>
				<p>Add all the games you want to sell and update the status to include <strong>For Trade</strong>. <br>
				In the comments include, just the numerical value you wish to sell the game for.<br>
				If you switch the view of your collection to just <strong>For Trade</strong>, you can supply condition information also.</p>
				<p>After you've updated your collection, return here and supply the following information:</p>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<form id="getUserInfo" action="">
					<div class="form-floating mb-3">
						<input type="text" name="user_name" id="user_name" class="form-control" placeholder="bggusername">
						<label for="user_name">BoardGameGeek Username</label>
					</div>
					<div class="form-floating mb-3">
						<input type="text" name="venmo_user" id="venmo_user" class="form-control" placeholder="my-venmo-username-c9965">
						<label for="venmo_user">Venmo Username</label>
						<div id="venmo_help" class="form-text">Example: my-venmo-username-c9965</div>
					</div>
					<div class="form-floating mb-3">
						<input type="text" name="seller_name" class="form-control" id="phone_number" placeholder="Your Name">
						<label for="seller_name">Your Name</label>
					</div>
					<div class="form-floating mb-3">
						<input type="text" name="phone_number" class="form-control" id="phone_number" placeholder="Mobile Number">
						<label for="phone_number">Your Cell Number</label>
						<div id="cell_help" class="form-text">Format the number the way you want it shown ( i.e. (801) 555-5555 )</div>
					</div>
					<button type="submit" class="btn btn-primary">Submit</button>
				</form>
			</div>
		</div>
	</div>
	</body>
</html>
<?php return;
} // No username

$username = $_GET['user_name'];
$qr_code = '';
if(!empty($_GET['venmo_user'])) {
	$string = trim(str_replace('@', '', $_GET['venmo_user']));
	$venmo_link = 'https://venmo.com/'.$string;
	$qr_code = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data='.urlencode($venmo_link);
}

$url = "https://boardgamegeek.com/xmlapi2/collection?username={$username}&trade=1&stats=1";

//setting the curl parameters.
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
// Following line is compulsary to add as it is:
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
$data = curl_exec($ch);
curl_close($ch);

//convert the XML result into array
$array_data = json_decode(json_encode(simplexml_load_string($data)), true);
if(!is_array($array_data['item'])) {
	echo $array_data[0];
	echo '<br>';
	echo '<button id="reload" class="btn btn-primary">Reload</button>';
	echo '</div></body></html>';
	return;
}

$games = [];
foreach($array_data['item'] as $key => $game) {
	if(empty($game['status']['@attributes']['fortrade'])) {
		continue;
	}
	$price = trim(preg_replace("/[^0-9]/", "", $game['comment']));
	$text = trim(preg_replace("/[^a-zA-Z]/", " ", $game['comment']));
	$condition = !empty($game['conditiontext']) ? $game['conditiontext'].$text : ($text ?: '');
	$games[] = [
		'title' => $game['name'] ?? 'Unkown Game',
		'num_plays' => $game['numplays'],
		'published' => $game['yearpublished'] ?? 'Unknown',
		'image' => $game['thumbnail'] ?? ($game['image'] ?? 'https://www.google.com/url?sa=i&url=https%3A%2F%2Fwww.iconsdb.com%2Fgray-icons%2Fquestion-mark-icon.html&psig=AOvVaw0SUeQ3_PwovFTaIq-2tdGi&ust=1627557313533000&source=images&cd=vfe&ved=0CAsQjRxqFwoTCPCajdHRhfICFQAAAAAdAAAAABAD'),
		'condition' => $condition,
		'price' => '$'.(trim(str_replace('$', '', ($price ?? 0)))),
		'rating' => number_format($game['stats']['rating']['average']['@attributes']['value'], 1),
		'min_players' => $game['stats']['@attributes']['minplayers'],
		'max_players' => $game['stats']['@attributes']['maxplayers'],
		'play_time' => $game['stats']['@attributes']['playingtime'],
	];
}

if(empty($games)) {
	echo '<h2>No Games Found! ¯\_(ツ)_/¯</h2>';
	return;
}
$i = 1;
$row_class = empty($qr_code) ? 'set-min' : '';
$second_col_class = !empty($qr_code) ? 'border-end-0' : '';
foreach($games as $game) { 
	if($i === 1) {
		echo '<div class="collection">';
	}?>
		<div class="row mb-1 <?=$row_class?>">
			<div class="img-col col border border-dark position-relative">
				<div class="position-absolute top-50 start-50 translate-middle h-100">
					<img src="<?=$game['image'] ?>" class="h-100">
				</div>
			</div>
			<div class="col border border-dark border-start-0 border-end-0 position-relative">
				<div class="row justify-content-between mt-2">
					<div class="col col-auto">
						<h5 class="d-inline-block text-truncate" style="max-width:300px;"><?=$game['title']?></h5>
					</div>
					<div class="col-2 col text-end">
						<h5><?=$game['price']?></h5>
					</div>
				</div>
				<div class="row">
					<div class="col seller-info <?=$second_col_class?> col-6">
						<p><b>Seller Name:</b> <?=$_GET['seller_name'] ?? ''?></p>
						<p><b>Cell Number:</b><?=$_GET['phone_number'] ?? ''?></p>
					</div>
					<div class="col seller-info  <?=$second_col_class?> col-6">
						<p><b>Year:</b> <?=$game['published']?> <strong>|</strong> <b>BGG Rating:</b> <?=$game['rating']?></p>
						<p><?=$game['min_players']?> - <?=$game['max_players']?> Players, <?=$game['play_time']?> Minutes</p>
					</div>
				</div>
				<div class="row">
					<div class="col col-12">
					<?php if(!empty($game['condition'])) { ?>
							<p><b>Condition:</b> <?=$game['condition']?></p>
						<?php } ?>
					</div>
				</div>
			</div>
		<?php if(!empty($qr_code)) { ?>
			<div class="img-col col border border-dark mx-auto d-block text-center">
				<h5>Venmo</h5>
				<img class="venmo-img" src="<?=$qr_code?>">
			</div>
		<?php } ?>
		</div>
<?php 
	if($i === 7) {
		echo '</div>';
	}
	$i++;
	if($i > 7) {
		$i = 1;
	}
} ?>
		</div>
	</div>
	</body>
</html>
