<!-- resources/views/deleted-notes.blade.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>已删除的笔记</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<h1>已删除的笔记列表</h1>
<ul id="deleted-notes-list">
    <!-- 通过AJAX填充已删除的笔记 -->
</ul>

<script>
    $(document).ready(function () {
        fetchDeletedNotes();
    });

    function fetchDeletedNotes() {
        $.ajax({
            url: '/api/v1/notes/deleted',
            method: 'GET',
            success: function (data) {
                var notesHtml = '';
                if (Array.isArray(data)) {
                    data.forEach(function (note) {
                        var tagsHtml = note.tags.map(function (tag) {
                            return tag.name;
                        }).join(', ');
                        notesHtml += '<li>' +
                            '<h2>' + note.title + '</h2>' +
                            '<p>' + note.content + '</p>' +
                            (tagsHtml ? '<p>Tags: ' + tagsHtml + '</p>' : '') +
                            '<button onclick="restoreNote(' + note.id + ')">恢复</button>' +
                            '</li>';
                    });
                    $('#deleted-notes-list').html(notesHtml);
                }
            }
        });
    }

    function restoreNote(noteId) {
        $.ajax({
            url: '/api/v1/notes/restore/' + noteId,
            method: 'POST',
            success: function (response) {
                alert(response.message);
                fetchDeletedNotes(); // 重新加载已删除的笔记列表
            }
        });
    }
</script>
</body>
</html>
