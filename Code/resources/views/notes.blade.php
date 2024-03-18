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
    <button type="addSubmit">添加笔记</button>
</form>

<script>
    // 设置AJAX头部，默认携带CSRF TOKEN
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    // 页面加载完毕后获取所有笔记
    $(document).ready(function () {
        fetchNotes();
    });

    // 获取所有笔记
    function fetchNotes() {
        $.ajax({
            url: '/api/v1/notes',
            method: 'GET',
            success: function (data) {
                console.log(data); // 打印响应数据以供检查
                var notesHtml = '';

                if (Array.isArray(data)) {
                    data.forEach(function (note, index) {
                        // 因为 tags 已知是数组，无需额外检查是否存在
                        var tagsHtml = note.tags.map(function (tag) {
                            return tag.name; // 使用 tag 的 'name' 属性
                        }).join(', ');
                        // 构建笔记和标签的 HTML
                        notesHtml += '<div class="note">' +
                            '<h2>' + note.title + '</h2>' +
                            '<p>' + note.content + '</p>' +
                            (tagsHtml ? '<br>Tags: ' + tagsHtml : '') +
                            '<button onclick="copyNote(' + note.id + ')">复制</button>' +
                            '<button onclick="deleteNote(' + note.id + ')">删除</button>' +
                            '</div>';
                        // 如果不是最后一个笔记，添加分割线
                        if (index < data.length - 1) {
                            notesHtml += '<hr>';
                        }
                    });
                    $('#notes-list').html(notesHtml);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                // 如果请求失败，处理错误情况
                console.error('Error fetching notes:', textStatus, errorThrown);
            }
        });
    }

    // 添加笔记
    $('#addNoteForm').on('addSubmit', function (e) {
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


</script>
</body>
</html>
