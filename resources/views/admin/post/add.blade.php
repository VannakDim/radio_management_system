@extends('admin.layout.admin')
@section('link')
    {{-- CK Editor --}}
    <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/44.0.0/ckeditor5.css" crossorigin>
    {{-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> --}}
@endsection

@php
    $categories = App\Models\Category::all();
@endphp

@section('main_body')
    <div class="py-12">
        <div class=" mx-auto">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-default">
                            <div class="card-header card-header-border-bottom">
                                <h2>Add Blog</h2>
                            </div>

                            <div class="card-body">
                                @if (session('error'))
                                    <div class="alert alert-danger">
                                        {{ session('error') }}
                                    </div>
                                @endif
                                <form id="uploadForm" action="{{ route('store.post') }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1">Blog title</label>
                                                <input type="text" name="title" class="form-control"
                                                    id="exampleInputEmail1" placeholder="Blog title">
                                                @error('title')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="category">Category:</label>
                                                <select name="categories[]" id="category" class="form-control">
                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->name }}">
                                                            {{ $category->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('category')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="tags">Tags:</label>
                                                <select name="tags[]" id="tags" class="form-control"
                                                    multiple="multiple">
                                                    @foreach ($tags as $tag)
                                                        <option value="{{ $tag->name }}">{{ $tag->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('tags')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="featured">Featured</label>
                                                <input type="checkbox" name="featured" id="featured"
                                                    value="@if (true) 1 @else 0 @endif">
                                            </div>

                                            <div class="form-group">
                                                <label for="exampleFormControlSelect12">Publish status:</label>
                                                <select name="status" class="form-control" id="exampleFormControlSelect12">
                                                    <option value="public">Public</option>
                                                    <option value="draft" selected>Draft</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="exampleInputEmail1">Blog image</label>
                                                <input type="file" name="image" id="input-image" class="form-control">
                                                @error('image')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="post-img" id="img-preview"
                                                style="display: flex; justify-content: center; align-items: center; background-image: url({{ asset('backend/assets/img/default-image.avif') }}); background-size: cover; background-position: center; width: 100%; height: 100%;">
                                                {{-- <img id="img-preview" src="" alt="Image Preview" style="max-width: 100%;max-height: 350px;object-fit: cover;"> --}}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Description</label>
                                        <input type="text" name="description" class="form-control"
                                            id="exampleInputEmail1" placeholder="Description">
                                        @error('description')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Content</label>

                                        <textarea id="editor" name="content" class="form-control" placeholder="Content" rows="5"></textarea>

                                        @error('content')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Loading indicator -->
                                    <div id="loading" style="display: none;">
                                        Uploading your post..., please wait...
                                    </div>

                                    <button type="submit" class="ladda-button btn btn-primary float-right"
                                        data-style="expand-left">
                                        <span class="ladda-label">Save!</span>
                                        <span class="ladda-spinner"></span>
                                    </button>
                                    <a href="{{ route('all.post') }}" class="btn btn-secondary float-right"
                                        style="margin-right: 6px">Back</a>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            $('#category').select2({
                tags: true, // Allow new tags
                placeholder: "Select or add tags",
                tokenSeparators: [',', ' ']
            });
            $('#tags').select2({
                tags: true, // Allow new tags
                placeholder: "Select or add tags",
                tokenSeparators: [',', ' ']
            });
        });
    </script>
    <script>
        // Get the input file and preview image elements
        const default_img = '{{ URL::to('') }}' + '/backend/assets/img/default-image.avif';
        const imageInput = document.getElementById('input-image');
        const previewImage = document.getElementById('img-preview');

        // Listen for the file input change event
        imageInput.addEventListener('change', function(event) {
            const file = event.target.files[0]; // Get the selected file

            if (file) {
                // Create a file reader
                const reader = new FileReader();

                // Load the image and set it as the src of the previewImage
                reader.onload = function(e) {
                    previewImage.style.backgroundImage = `url('${e.target.result}')`;
                    previewImage.style.display = 'block'; // Make the image visible
                };

                // Read the file as a data URL
                reader.readAsDataURL(file);
            } else {
                // If no file is selected, hide the image preview
                previewImage.style.backgroundImage = `url('${default_img}')`;
                previewImage.style.display = 'none';
            }
        });
    </script>
    <script src="https://cdn.tiny.cloud/1/qdi8ljnwutu3zjh290nqmze8oo8w5x9wqh925tzk9eyqpqmk/tinymce/7/tinymce.min.js"
        referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: 'textarea',
            plugins: 'autolink lists link image charmap print preview hr anchor pagebreak',
            toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | chords | bullist numlist outdent indent | link image | print preview media fullpage | forecolor backcolor emoticons | charmap | pagebreak | help',
            setup: function(editor) {
                editor.ui.registry.addButton('chords', {
                    text: 'Add Chord',
                    onAction: function() {
                        const chord = prompt('Enter chord:');
                        if (chord) {
                            editor.insertContent(`[${chord}]`);
                        }
                    },
                });
            },
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#uploadForm').on('submit', function(e) {
                e.preventDefault(); // Prevent the default form submission

                // Show loading indicator
                $('#loading').show();

                // Submit the form via AJAX
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        // Hide loading indicator
                        $('#loading').hide();
                        window.location.href =
                        '{{ route('all.post') }}'; // Redirect to the "home" route
                        alert(response.message);
                    },
                    error: function(xhr) {
                        // Hide loading indicator
                        $('#loading').hide();
                        alert('Upload failed: ' + xhr.responseJSON.message);
                    }
                });
            });
        });
    </script>
@endsection
