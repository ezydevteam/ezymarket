@php
    $directFileToBig = translate('file is too big max file size: {{maxFilesize}}MiB.');
    $dictResponseError = translate('Server responded with {{statusCode}} code.');
@endphp
<script>
    "use strict";
    window.uploadOptions = {
        dictDefaultMessage: "{{ translate('Drop files here to upload') }}",
        dictFallbackMessage: "{{ translate('Your browser does not support drag and drop file uploads.') }}",
        dictFallbackText: "{{ translate('Please use the fallback form below to upload your files like in the olden days.') }}",
        dictFileTooBig: "{{ $directFileToBig }}",
        dictInvalidFileType: "{{ translate('You cannot upload files of this type.') }}",
        dictResponseError: "{{ $dictResponseError }}",
        dictCancelUpload: "{{ translate('Cancel upload') }}",
        dictCancelUploadConfirmation: "{{ translate('Are you sure you want to cancel this upload?') }}",
        dictRemoveFile: "{{ translate('Remove file') }}",
        dictMaxFilesExceeded: "{{ translate('You can not upload any more files.') }}",
        errors: {
            no_files_uploaded: "{{ translate('Upload at least one file') }}",
            file_duplicate: "{{ translate('Duplicate files not allowed') }}",
            file_empty: "{{ translate('Empty files cannot be uploaded') }}",
            main_file_required: "{{ translate('Main file required') }}",
            main_url_required: "{{ translate('External file URL required') }}",
            valid_url_required: "{{ translate('Enter a valid URL') }}",
            max_files_exceeded: "{{ translate('Maximum upload limit reached.') }}",
            max_file_size_exceeded: "{{ translate('File size exceeds the limit.') }}",
            preview_image_required: "{{ translate('Preview image required.') }}",
        },
        format_bytes: ["{{ translate('B') }}", "{{ translate('KB') }}", "{{ translate('MB') }}", "{{ translate('GB') }}", "{{ translate('TB') }}"]
    };
</script>
