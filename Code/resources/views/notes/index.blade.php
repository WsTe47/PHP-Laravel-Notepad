<!DOCTYPE html>
<html>
<head>
    <title>记事本</title>
</head>
<body>
<h1>笔记列表</h1>

<ul>
    @foreach ($notes as $note)
        <li>
            <strong>{{ $note->title }}</strong>: {{ $note->content }}
            <br>Tags: {{ $note->tags->pluck('name')->implode(', ') }}
            <br>
            <form action="{{ route('notes.copy', $note->id) }}" method="post">
                @csrf
                <button type="submit">复制</button>
            </form>
            <form action="{{ route('notes.delete', $note->id) }}" method="post">
                @method('DELETE')
                @csrf
                <button type="submit">删除</button>
            </form>
        </li>
    @endforeach
</ul>

<h2>添加笔记</h2>
<form id="addNoteForm">
    <input type="text" id="noteTitle" placeholder="标题" required>
    <textarea id="noteContent" placeholder="内容" required></textarea>
    <input type="text" id="noteTags" placeholder="标签，用逗号分隔">
    <button type="submit">添加笔记</button>
</form>

<!-- 注意：在生产环境中，X-CSRF-TOKEN 应该以更安全的方式传递 -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
    document.addEventListener('DOMContentLoaded', function() {
        fetchNotes();

        document.getElementById('addNoteForm').addEventListener('submit', function(event) {
            event.preventDefault();
            addNote();
        });
    });
    //
    // function fetchNotes() {
    //     fetch('/notes')
    //         .then(response => {
    //             if (!response.ok) {
    //                 throw new Error('Network response was not ok');
    //             }
    //             return response.json();
    //         })
    //         .then(data => {
    //             const notesList = document.getElementById('notesList');
    //             notesList.innerHTML = ''; // 清空现有列表
    //             data.forEach(note => {
    //                 const listItem = document.createElement('li');
    //                 // 使用模板字符串构建每个笔记的HTML结构
    //                 listItem.innerHTML = `
    //                 <strong>${note.title}</strong>: ${note.content}
    //                 <br>Tags: ${note.tags.map(tag => tag.name).join(', ')}
    //                 <br>
    //                 <button onclick="copyNote(${note.id})">复制</button>
    //                 <button onclick="deleteNote(${note.id})">删除</button>
    //             `;
    //                 notesList.appendChild(listItem); // 将笔记项添加到列表中
    //             });
    //         })
    //         .catch(error => {
    //             console.error('Error:', error);
    //         });
    // }

    function addNote() {
        const title = document.getElementById('noteTitle').value;
        const content = document.getElementById('noteContent').value;
        const tags = document.getElementById('noteTags').value.split(',').map(tag => tag.trim());

        fetch('/notes/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ title, content, tags })
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                alert(data.message); // 弹窗提示信息
                fetchNotes(); // 刷新笔记列表
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding note: ' + error.message); // 弹窗显示错误信息
            });

    }

    function copyNote(noteId) {
        fetch(`/notes/copy/${noteId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                fetchNotes();
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    function deleteNote(noteId) {
        fetch(`/notes/delete/${noteId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                fetchNotes();
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    function restoreNote(noteId) {
        // 示例：您需要实现一个后端 API 端点来处理恢复笔记的逻辑
        fetch(`/notes/restore/${noteId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                fetchNotes();
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
</script>

</body>
</html>
