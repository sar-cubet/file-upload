<!DOCTYPE html>
<html>
<head>
	<title>File Upload</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
	<style>
		body {
			font-family: Arial, sans-serif;
			margin: 0;
			padding: 0;
			background-color: white;
			background-size: cover;
			background-position: center;
             height: 100%;
		}
		.container {
			display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0 auto;
            margin-top: 50px;
            max-width: 80%;
            background-color: #edebf1;
            padding: 30px;
            box-shadow: 0 0 10px rgb(0 0 0 / 20%);
            border-radius: 5px;
            height: 80%;
            min-height: 80vh;
		}
		h1 {
			margin-bottom: 20px;
			text-align: center;
		}
		form {
			display: flex;
			flex-direction: column;
			align-items: center;
			border: 1px solid #ccc;
			padding: 20px;
			margin-bottom: 20px;
			box-shadow: 0 0 10px rgba(0,0,0,0.2);
			width: 400px;
		}
		form label {
			font-weight: bold;
			margin-bottom: 10px;
			text-align: left;
			width: 100%;
		}
		form input[type="file"] {
			margin-bottom: 20px;
		}
		form button {
			background-color: #4CAF50;
			color: white;
			padding: 10px;
			border: none;
			border-radius: 5px;
			cursor: pointer;
			font-weight: bold;
			margin-top: 10px;
		}
		.file-list {
			display: flex;
			flex-direction: column;
			align-items: center;
			border: 1px solid #ccc;
			padding: 20px;
			box-shadow: 0 0 10px rgba(0,0,0,0.2);
			width: 400px;
			list-style: none;
			margin: 0;
		}
		.file-list li {
			margin-bottom: 10px;
			display: flex;
			align-items: center;
		}
		.file-list li a {
			color: #4CAF50;
			text-decoration: none;
			margin-left: 10px;
		}
	</style>

    <style>
        .file-upload-btn {
            width: 100%;
            margin: 0;
            color: #fff;
            background: #1FB264;
            border: none;
            padding: 10px;
            border-radius: 4px;
            border-bottom: 4px solid #15824B;
            transition: all .2s ease;
            outline: none;
            text-transform: uppercase;
            font-weight: 700;
        }

        .file-upload-btn:hover {
            background: #1AA059;
            color: #ffffff;
            transition: all .2s ease;
            cursor: pointer;
        }

        .file-upload-btn:active {
            border: 0;
            transition: all .2s ease;
        }

        .file-upload-content {
            width: 100%;
            display: none;
            text-align: center;
        }

        .file-upload-input {
            position: absolute;
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            outline: none;
            opacity: 0;
            cursor: pointer;
        }

        .image-upload-wrap {
            margin-top: 20px;
            border: 4px dashed #1FB264;
            position: relative;
        }

        .image-dropping,
        .image-upload-wrap:hover {
            background-color: #1FB264;
            border: 4px dashed #ffffff;
        }

        .image-title-wrap {
            padding: 0 15px 15px 15px;
            color: #222;
        }

        .drag-text {
            text-align: center;
        }

        .drag-text h3 {
            font-weight: 100;
            text-transform: uppercase;
            color: #15824B;
            padding: 60px 0;
        }

        .file-upload {
            width: 45%;
        }

        .file-upload-image {
            max-height: 252px;
            margin: auto;
            padding: 20px;
        }

        .save-image{
            display:none;
        }

        .remove-image {
            margin: 0;
            color: #fff;
            background: #cd4535;
            border: none;
            padding: 10px;
            border-radius: 4px;
            border-bottom: 4px solid #b02818;
            transition: all .2s ease;
            outline: none;
            text-transform: uppercase;
            font-weight: 700;
        }

        .remove-image:hover {
            background: #c13b2a;
            color: #ffffff;
            transition: all .2s ease;
            cursor: pointer;
        }

        .remove-image:active {
            border: 0;
            transition: all .2s ease;
        }

        .quality-btn{
            width: 143px;
        }

        .no-padding{
            padding:0 !important;
        }

    </style>

    <style>
        .input-group {
            margin-top: 30px;
            position: relative;
        }

        .input-group {
            position: relative;
        }

        .input-group-addon {
            border: none;
        }

        .linkname {
            display: none;
        }

        #copyButton {
            cursor: pointer;
            background: #f1bb3a;
        }

        #copyTarget {
            border-left: none;
        }


    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

    
</head>
<body>
	<div class="container">
        <div class="alert alert-dismissible fade" id="alert" role="alert" style="width:80%">
            <div class="alert-div">

            </div>
            <div class="alert-ul">
                
            </div>
            <button type="button" class="close" aria-label="Close" onclick="closeAlert()">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        
		<h1>File Upload</h1>
            <div class="file-upload">
                <div class="image-upload-wrap">
                    <input class="file-upload-input" name="file" type='file' onchange="readURL(this);" accept="" />
                    <div class="drag-text">
                    <h3>Drag and drop or click to add an Image</h3>
                    </div>
                </div>
                <div class="file-upload-content">
                    <img class="file-upload-image" src="#" alt="your image" />
                    <div class="image-title-wrap">
                    <button type="button" onclick="removeUpload()" class="remove-image">Remove Image</button>
                    </div>
                </div><br>
                
                <div class="form-group save-image">
                    <label for="quality" class="col-sm-2 col-form-label no-padding">Quality</label>
                    <div class="btn-group btn-group-toggle col-sm-8 no-padding" data-toggle="buttons">
                        <label class="btn btn-primary quality-btn">
                            <input type="radio" name="quality" id="quality_option1" value="excellent"> Excellent
                        </label>
                        <label class="btn btn-primary quality-btn active">
                            <input type="radio" name="quality" id="quality_option2" value="moderate" checked> Moderate
                        </label>
                        <label class="btn btn-primary quality-btn">
                            <input type="radio" name="quality" id="quality_option3" value="average"> Average
                        </label>
                    </div>
                </div>
                <div class="form-group save-image">
                    <button class="file-upload-btn" type="button" onclick="saveImage(event)">Save Image</button>
                </div>
            </div><br><br>

            <div style="width: 90%;">
                <table id="example" class="display" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th style="width: 15px !important;">No</th>
                            <th style="width: 15px !important;">Image</th>
                            <th>Link</th>
                            <th style="width: 70px !important;">Actions</th>
                        </tr>
                    </thead>
            
                    <tbody id="image-tbody">
                
                    </tbody>
                </table>
            </div>
	</div>
</body>
<script class="jsbin" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
    initTable()
    function readURL(input) {
        if (input.files && input.files[0]) {

            var reader = new FileReader();

            reader.onload = function(e) {
                $('.image-upload-wrap').hide();
                $('.file-upload-image').attr('src', e.target.result);
                $('.file-upload-content').show();
                $('.save-image').show();
                {{-- let fileName = input.files[0].name.split('.').shift(); --}}
                {{-- $('#name').val(fileName) --}}
            };

            reader.readAsDataURL(input.files[0]);

        } else {
            removeUpload();
        }
    }

    function removeUpload() {
        $('.file-upload-input').replaceWith($('.file-upload-input').clone());
        $('.file-upload-content').hide();
        $('.image-upload-wrap').show();
        $('.save-image').hide();
    }

    $('.image-upload-wrap').bind('dragover', function () {
        $('.image-upload-wrap').addClass('image-dropping');
    });

    $('.image-upload-wrap').bind('dragleave', function () {
        $('.image-upload-wrap').removeClass('image-dropping');
    });

    function saveImage(event){
        event.preventDefault();

        var formData = new FormData();
        var csrfToken = $('meta[name="csrf-token"]').attr('content');;
        formData.append('file', $('input[name="file"]')[0].files[0]);
        formData.append('name', $('input[name="name"]').val());
        formData.append('quality', $('input[name="quality"]:checked').val());
        

        console.log(csrfToken)
        
        $.ajax({
            headers:{
                'X-CSRF-TOKEN': csrfToken
            },
            type: "POST",
            url: "{{route('uploadProcess')}}",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if(response.status == 1){
                    $('#alert').addClass('alert-success')
                    $('.alert-div').text('Image successfully saved');
                    showAlert()
                    removeUpload()
                    
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                }else{
                    if(response.errors.image.length > 0 ){
                        let ul = '<ul>'
                        for(let i=0; i<response.errors.image.length; i++){
                            ul += `<li>${response.errors.image[i]}</li>`
                        }
                        ul += '</ul>'

                        closeAlert()
                        $('#alert').addClass('alert-danger')
                        $('.alert-ul').append(ul)
                        $('.alert-div').text('Please correct the errors');
                        showAlert()
                        setTimeout(function() {
                            closeAlert()
                        }, 5000);
                    }else{
                        $('.alert-ul').empty()
                        $('.alert-div').text('')
                        closeAlert()
                    }
                }
            },
            error: function(error) {
                console.log(error);
            }
        });
    }

    function initTable(){
        $.ajax({
                type: "GET",
                url: "{{route('getFiles')}}",
                success: function(response) {
                    if(response.status == 1){
                        console.log(response)

                        var tableData = response.data
                        var table = $('#example').DataTable();

                        for(let i=0; i<tableData.length; i++){
                            {{-- let imagePath = "{{ asset('') }}" + tableData[i].path --}}
                            let imagePath = tableData[i].path
                        
                            let col1 = `<td>${i+1}</td>`
                            let col2 = `<img src="${imagePath}" alt="Image" width="120px">`
                            let col3 = 
                                    `
                                    <div class="input-group">
                                        <input type="text" id="copyTarget_${i}" class="form-control" value="${imagePath}" disabled>
                                        <span id="copyButton" class="input-group-addon btn" title="Click to copy" onclick="copyToClipboard(${i})">
                                        <i class="fa fa-clipboard" aria-hidden="true" onclick="changeText(this)">Copy</i>
                                        </span>
                                    </div>
                                    `
                            let col4 = 
                                    `<div class="btn-group" role="group">
                                        <button id="btnGroupDrop1" type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Action
                                        </button>
                                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                            <button class="dropdown-item btn btn-primary" onclick="openInNewTab('${imagePath}')">View</button>
                                            <button class="dropdown-item btn btn-primary" onclick="downloadImage('${imagePath}')">Download</button>
                                        </div>
                                    </div>`

                            let row = [col1, col2, col3, col4]
                            table.row.add(row).draw()
                        }

                        setTimeout(function() {
                            $('#example').DataTable();
                        }, 100);
                    }else{
                        
                    }
                },
                error: function(error) {
                    console.log(error);
                }
        });
    }

    function downloadImage(url) {
        fetch(url)
            .then(response => response.blob())
            .then(blob => {

            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            
            const filename = getFilenameFromURL(url);
            link.download = filename;
            
            link.click();
            URL.revokeObjectURL(link.href);
        })
        .catch(error => {
            console.error('Error downloading image:', error);
        });
    }

    function getFilenameFromURL(url) {
        const lastSlashIndex = url.lastIndexOf('/');
        return url.substring(lastSlashIndex + 1);
    }

    function openInNewTab(url) {
        window.open(url, '_blank');
    }


    function closeAlert(){
        $('.alert-ul').empty()
        $('.alert-div').text('')
        $('#alert').removeClass('show')
    }

    function showAlert(){
        $('#alert').addClass('show')
    }

    function changeText(ele)
    {
        ele.textContent = 'Copied'

        setTimeout(function() {
            ele.textContent = 'Copy'
        }, 3000);
    }

    function copyToClipboard(index) {
        var target = document.getElementById("copyTarget_"+index)
        navigator.clipboard.writeText(target.value)
    }
    
</script>

</html>
