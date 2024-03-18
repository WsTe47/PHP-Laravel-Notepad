<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>记事本</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- 引入 jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<h1>笔记列表</h1>
<ul id="notes-list">
    <!-- 笔记列表将由AJAX填充 -->
</ul>

<h2>添加笔记</h2>
<form id="addNoteForm">
    @csrf
    <input type="text" name="title" placeholder="Note Title">
    <textarea name="content" placeholder="Note Content"></textarea>
    <input type="text" name="tags[]" placeholder="Tag 1">
    <input type="text" name="tags[]" placeholder="Tag 2">
    <!-- 可以根据需要添加更多的标签输入框 -->
    <button type="submit">添加笔记</button>
</form>

<script>
    // 设置AJAX头部，默认携带CSRF TOKEN
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // 获取所有笔记
    function fetchNotes() {
        $.ajax({
            url: '/api/v1/notes',
            method: 'GET',
            success: function (data) {
                var notesHtml = '';
                $.each(data, function (index, note) {
                    notesHtml += '<li>' +
                        '<strong>' + note.title + '</strong>: ' + note.content +
                        '<br>Tags: ' + note.tags.map(tag => tag.name).join(', ') +
                        '<br>' +
                        '<button onclick="copyNote(' + note.id + ')">复制</button>' +
                        '<button onclick="deleteNote(' + note.id + ')">删除</button>' +
                        '</li>';
                });
                $('#notes-list').html(notesHtml);
            }
        });
    }

    // 添加笔记
    $('#addNoteForm').on('submit', function (e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
            url: '/api/v1/notes/add',
            method: 'POST',
            data: formData,
            success: function (response) {
                alert(response.message);
                fetchNotes(); // 重新加载笔记列表
            }
        });
    });

    // 复制笔记
    function copyNote(noteId) {
        $.ajax({
            url: '/api/v1/notes/copy/' + noteId,
            method: 'POST',
            success: function (response) {
                alert(response.message);
                fetchNotes(); // 重新加载笔记列表
            }
        });
    }

    // 删除笔记
    function deleteNote(noteId) {
        $.ajax({
            url: '/api/v1/notes/delete/' + noteId,
            method: 'DELETE',
            success: function (response) {
                alert(response.message);
                fetchNotes(); // 重新加载笔记列表
            }
        });
    }

    // 页面加载完毕后获取所有笔记
    $(document).ready(function () {
        fetchNotes();
    });
</script>
</body>
</html>
