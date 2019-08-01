<?php
	require 'general.php';

	function delete_directory($dirname)
    {
        if (is_dir($dirname)) {
            $dir_handle = opendir($dirname);
        }
        if (!$dir_handle) {
            return false;
        }
        while ($file = readdir($dir_handle)) {
            if ('.' != $file && '..' != $file) {
                if (!is_dir($dirname.'/'.$file)) {
                    unlink($dirname.'/'.$file);
                } else {
                    delete_directory($dirname.'/'.$file);
                }
            }
        }
        closedir($dir_handle);

        return true;
	}

    delete_directory($vid_dir.'/'.$user_id . '/images');
    delete_directory($vid_dir.'/'.$user_id . '/videos');

    require 'fetching_images.php';
    $image_path = "${vid_dir}/${user_id}/images/";
    $video_path = "${vid_dir}/${user_id}/videos/";
    $fi = new FilesystemIterator($image_path, FilesystemIterator::SKIP_DOTS);
    $iloop = iterator_count($fi);
    $images = array();

    $fi = new FilesystemIterator($video_path, FilesystemIterator::SKIP_DOTS);
    $vloop = iterator_count($fi);
    $videos = array();

	$dir_audio = 'audio';
    $afi = new FilesystemIterator($dir_audio, FilesystemIterator::SKIP_DOTS);
    $aloop = iterator_count($fi);

    if (isset($_POST['upload_images'])) {
        // File upload configuration
        $insertValues = '';
        $allowTypes = ['jpg', 'mp4'];
        
        // print_r($_FILES['upload-images']['name']);
        // print_r($_FILES['upload-images']['tmp_name']);

        $statusMsg = $errorMsg = $errorUpload = $errorUploadType = '';
        if (!empty(array_filter($_FILES['upload-images']['name']))) {
            foreach ($_FILES['upload-images']['name'] as $key => $val) {
                
                $filename = $val;
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if ($ext == "mp4") {
                    ++$vloop;
                    $fileName = 'vid'.$vloop.'.mp4';
                    $targetDir = $video_path;
                    array_push($videos, $fileName);
                } else {
                    ++$iloop;
                    $fileName = 'pic'.$iloop.'.jpg';
                    $targetDir = $image_path;
                    array_push($images, $fileName);
                }
                $targetFilePath = $targetDir.$fileName;
                // die(var_dump($targetFilePath));
                //echo $targetFilePath;

                // Check whether file type is valid
                $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
                if (in_array($fileType, $allowTypes)) {
                    // Upload file to server
                    if (move_uploaded_file($_FILES['upload-images']['tmp_name'][$key], $targetFilePath)) {
                        $insertValues .= "('".$fileName."', NOW()),";
                    } else {
                        $errorUpload .= $_FILES['upload-images']['name'][$key].', ';
                    }
                } else {
                    echo "<script>
							alert('Only jpg images are allowed');
						</script>";
                }
            }
            // Display status message
            echo "<script>
				alert('Images Uploaded Successfully');
            </script>";
        }
    }
?>

<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">

	<!-- Page Title -->
	<title>Video Making</title>

	<!-- Stylesheet links -->
	<!-- <link rel="stylesheet" href="css/bootstrap.min.css"> -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-fileinput/4.5.2/css/fileinput.css" />
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css">
    <link rel="stylesheet" href="css/videojs.markers.min.css">
    <link rel="stylesheet" href="css/video-js.min.css">
	<link rel="stylesheet" href="css/stylesheet.css">

</head>

<body>


	<div class="container-fluid">

		<div class="container border rounded mt-3">
			<form method="post">

				<div class="row mt-md-4 mt-xs-2">
					<div class="col-xs-12 col-sm-9 col-md-9">
						<input class="form-control" name="url" type="url" placeholder="Enter the URL" required>
					</div>

					<div class="col-xs-12 col-sm-3 col-md-3 fetchBox">
						<button class="btn btn-primary mt-xs-3 w-100" type="submit" name="fetch">Fetch
							Images</button>
					</div>
				</div>
			</form>
			<!-- URL INPUT AND FETCH ROW ENDS HERE -->
			<form method="post" enctype="multipart/form-data">
				<input type="hidden" name="upload_images" value="1">
				<div class="form-group">
					<label for="upload-images" class="control-label">Upload your images</label>
					<div class="file-loading">
						<input id="upload-images" name="upload-images[]" type="file" multiple>
					</div>
				</div>
			</form>

			<div class="row ">
				<div class="col-md-6 mt-3">

					<div class="card p-3">
						<h6 class="text-capitalize">1. Choose your image</h6>
						<div class="row" id="uploadContainer">
                            <?php	for ($i = 1; $i <= $iloop; ++$i) { ?>
                                <div class="image-panel col-4 col-sm-3 mt-2" overlay-text="" effect="zoom-in">
                                    <img src="<?php echo $image_path; ?>pic<?php echo $i; ?>.jpg"
                                        val="<?php echo $image_path; ?>pic<?php echo $i; ?>.jpg"
                                        class="img-thumbnail" />
                                    <a class="edit-image" data-toggle="modal" href="#modal-image-edit">
                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                    </a>
                                    <div class="wrapper">
                                        <div class="wraperNumber">1</div>
                                    </div>
                                    <p class="image-animation">
                                        <i class="fas fa-external-link-alt"></i>
                                        <span value="slow-zoom">Slow zoom<span>
                                    </p>
                                    <p class="image-overlay-text"><i class="fas fa-text-height"></i><span><span></p>
                                </div>
							<?php } ?>
                        </div>
                        <p class="subtitle fancy <?php if ($vloop == 0) echo "display-none"?>"><span>Video Clips</span></p>
                        <div class="row" id="uploadContainerVideo">
                            <?php	for ($i = 1; $i <= $vloop; ++$i) { ?>
                                <div class="image-panel col-4 col-sm-3 mt-2" overlay-text="" effect="zoom-in">
                                    <video class="img-thumbnail">
                                        <source src="<?php echo $video_path; ?>vid<?php echo $i; ?>.mp4" type="video/mp4">
                                        <div class="file-preview-other">
                                            <span class="file-other-icon"><i class="glyphicon glyphicon-file"></i></span>
                                        </div>
                                    </video>
                                    <a class="edit-video" data-toggle="modal" href="#modal-video-edit" id="">
                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                    </a>
                                    <a class="remove-video">
                                        <span class="glyphicon glyphicon-minus" aria-hidden="true"></span>
                                    </a>
                                    <a class="add-video">
                                        <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                                    </a>
                                    <input type="hidden" value="" id="saved-start-time">
                                    <input type="hidden" value="" id="saved-end-time">
                                    <div class="wrapper">
                                        <div class="wraperNumber">1</div>
                                    </div>
                                    <!-- <p class="image-animation"><i class="fas fa-external-link-alt"></i><span
                                            value="slow-zoom">Slow zoon<span>
                                    </p> -->
                                    <p class="image-overlay-text"><i class="fas fa-text-height"></i><span><span></p>
                                </div>
							<?php } ?>
                        </div>
					</div>

					<!-- ROW FOR SECTION 1 ENDS HERE -->
					<div class="card p-3 mt-xs-2">
						<h6>2.Text options</h6>

						<form action="">

							<div class="form-group">
								<label for="top-bar-text" class="text-capitalize text-secondary">
									top bar text(optional)
								</label>

								<input id="top-bar-text" class="form-control" type="text">
							</div>

							<div class="form-group">
								<label for="bottom-bar-text" class="text-capitalize text-secondary">
									bottom bar text(optional)
								</label>

								<input id="bottom-bar-text" class="form-control" type="text">
							</div>

							<div class="form-group">
								<label for="end-screen-text" class="text-capitalize text-secondary">
									ending screen text(optional)
								</label>

								<input id="end-screen-text" class="form-control" type="text">

							</div>

							<div class="form-group">
								<label for="your-brand-name" class="text-capitalize text-secondary">
									Your Brand Name(optional)
								</label>

								<input id="your-brand-name" class="form-control mt-3" type="text"
									placeholder="Your Brand Name">

							</div>

						</form>
					</div>
					<div class="card p-3 mt-xs-2">
						<h6>3. Image Fit</h6>

						<form action="">

							<div class="form-group">
								<select id="select-image-fit" class="form-control" id="sel1">
									<option>Aspect Fill</option>
									<option>Aspect Fit</option>
								</select>
							</div>

						</form>
					</div>
				</div>

				<div class="col-md-6 mb-5">
					<div class="card p-3 mt-3">
						<h6>4. Select Your Sound Track</h6>
						<select id="select-sound" class="form-control" id="sel1">
							<option>Select audio file</option>
							<?php
                                $dir_path = $dir_audio.'/';
                                $options = '';
                                if (is_dir($dir_path)) {
                                    $files = opendir($dir_path);

                                    if ($files) {
                                        while (false !== ($file_name = readdir($files))) {
                                            if ('.' != $file_name && '..' != $file_name) {
                                                // select option with files names
                                                echo '<option>'.$file_name.'</option>';
                                            }
                                        }
                                    }
                                }
                            ?>
						</select>
					</div>

					<div class="card p-3 mt-xs-2">

						<h6>5. Seconds Per Frame</h6>
						<select class="form-control" id="select-per-frame">
							<option>1</option>
							<option>2</option>
							<option>3</option>
							<option>4</option>
						</select>
                    </div>
                    
                    <div class="card p-3 mt-xs-2">

						<h6>6. Background Color</h6>
						<select class="form-control" id="select-bg-color">
							<option value="1">Black</option>
							<option value="2">White</option>
						</select>
					</div>

					<div class="card p-3 mt-xs-2 d-flex justify-content-center">
						<h6>7.Generate Your Video</h6>

						<button id="btn-generate-video" data-loading-text="Generating ..."
							class="btn btn-primary mt-xs-3 w-75">Generate
							Video</button>
					</div>
					<div class="card p-3 mt-xs-2">

						<h6>8. Video View</h6>
						<video id='result-video' class='video-js' controls>
							<source type='video/mp4'>
						</video>
					</div>
				</div>
			</div>

			<div class="row gap"></div>
		</div>

	</div>

	<!-- Image Modal -->
	<div class="modal fade" id="modal-image-edit" role="dialog">
		<div class="modal-dialog">

			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4>Edit Animation</h4>
				</div>
				<div class="modal-body">
					<form role="form">
						<div class="form-group">
							<label for="animation">Animation</label>
							<select id="animation" class="form-control" value="slow-zoom">
								<option value="slow-zoom">Slow zoom</option>
								<option value="pan-down-and-right">Pan down and right</option>
								<option value="pan-down-and-left">Pan down and left</option>
								<option value="pan-up-and-right">Pan up and right</option>
								<option value="pan-up-and-left">Pan up and left</option>
							</select>
						</div>
						<div class="form-group">
							<label for="overlay-text">Overlay Text</label>
							<input type="text" class="form-control" id="overlay-text">
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button id="animation-submit" type="submit" class="btn btn-default btn-default"
						data-dismiss="modal">Save</button>
					<button type="submit" class="btn btn-default btn-default" data-dismiss="modal">Cancel</button>
				</div>
			</div>
		</div>
    </div>
    
    <!-- Video Modal -->
    <div class="modal fade" id="modal-video-edit" role="dialog">
		<div class="modal-dialog">

			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4>Edit Animation</h4>
				</div>
				<div class="modal-body">
					<form role="form">
						<div class="form-group">
							<label for="animation">Animation</label>
							<video autoplay class="video-js vjs-default-skin" controls="" data-setup='{"width": 568, "height": 360, "autoplay": true}' height="360" id="video-active" preload="auto" width="568px">
                                <source src="" type="video/mp4"/>
                            </video>
                        </div>
                        <div class="form-group text-center">
                            <button id="set-start-time" type="button" class="btn btn-default">Set Start</button>
                            <button id="set-end-time" type="button" class="btn btn-default">Set End</button>
                        </div>
                        <div class="form-group col-md-6" style="padding-left: 0px; padding-right: 15px">
							<label for="overlay-text">Start time(second)</label>
							<input type="text" class="form-control" id="start-time" disabled>
                        </div>
                        <div class="form-group col-md-6" style="padding-right: 0px">
							<label for="overlay-text">End time(second)</label>
							<input type="text" class="form-control" id="end-time" disabled>
						</div>
						<div class="form-group">
							<label for="overlay-text">Overlay Text</label>
							<input type="text" class="form-control" id="overlay-video-text">
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button id="animation-video-submit" type="submit" class="btn btn-default" data-dismiss="modal">Save</button>
					<button type="submit" class="btn btn-default" data-dismiss="modal">Cancel</button>
				</div>
			</div>
		</div>
	</div>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-fileinput/4.5.2/js/fileinput.js"></script>
    <script src="js/video.min.js"></script>
    <script src="js/videojs-markers.min.js"></script>
    <script>
        var user_id = "<?=$user_id?>";
        var vid_dir = "users";
    </script>
	<script src="js/app.js"></script>

</body>

</html>