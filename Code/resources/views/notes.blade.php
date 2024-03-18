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
    <!-- 在输入框中设置最大长度 -->
    <input type="text" name="title" placeholder="Note Title" maxlength="10">
    <textarea name="content" placeholder="Note Content" maxlength="100"></textarea>
    <input type="text" name="tags[]" placeholder="Tag 1" maxlength="5">
    <input type="text" name="tags[]" placeholder="Tag 2" maxlength="5">

    <!-- 可以根据需要添加更多的标签输入框 -->
    <button type="submit">添加笔记</button>
</form>

<script>
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
    $('#addNoteForm').on('submit', function (e) {
        e.preventDefault();
        var title = $('input[name="title"]').val();
        var content = $('textarea[name="content"]').val();
        // 校验标题是否含有符号
        if(/[^a-zA-Z0-9\s]/.test(title)) {
            alert('标题不允许含有符号！');
            return; // 如果含有符号，停止表单提交
        }
        // 校验标题长度，虽然已经通过HTML限制，但是额外校验增强安全性
        if(title.length > 10) {
            alert('标题长度不能超过10个字符！');
            return;
        }
        // 校验正文长度
        if(content.length > 100) {
            alert('正文长度不能超过100个字符！');
            return;
        }
        // 校验每个标签的长度
        var tagsOverLength = $('input[name="tags[]"]').toArray().some(function(tag) {
            return tag.value.length > 5;
        });
        if(tagsOverLength) {
            alert('每个标签长度不能超过5个字符！');
            return;
        }

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
