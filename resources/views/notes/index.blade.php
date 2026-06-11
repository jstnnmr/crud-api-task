@extends('layouts.app')
@section('title', 'Notes | EaseTask')

@push('styles')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.min.css" rel="stylesheet">
<style>
    .container { max-width: 1280px; margin: 0 auto; padding: 2rem; position: relative; z-index: 1; }
    .page-title { font-family: 'Playfair Display', serif; font-size: 1.65rem; font-weight: 600; color: var(--text); display: flex; align-items: center; gap: .5rem; margin-bottom: .25rem; }
    .page-subtitle { font-size: .68rem; color: var(--text-muted); letter-spacing: .1em; text-transform: uppercase; opacity: .7; margin-bottom: 1.75rem; }
    .notes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1.25rem; }
    .sticky {
        border-radius: var(--radius);
        padding: 1.25rem;
        min-height: 200px;
        display: flex;
        flex-direction: column;
        position: relative;
        cursor: pointer;
        transition: transform .15s, box-shadow .2s;
        box-shadow: 0 4px 16px rgba(0,0,0,.15);
        border: 1px solid rgba(0,0,0,.05);
    }
    .sticky:hover { transform: translateY(-3px); box-shadow: 0 8px 28px rgba(0,0,0,.2); }
    .sticky-title { font-family: 'Playfair Display', serif; font-size: .95rem; font-weight: 600; margin-bottom: .5rem; word-break: break-word; }
    .sticky-preview { flex: 1; font-size: .78rem; opacity: .8; overflow: hidden; word-break: break-word; }
    .sticky-preview p { margin: 0; }
    .sticky-footer { display: flex; align-items: center; justify-content: space-between; margin-top: .75rem; padding-top: .75rem; border-top: 1px solid rgba(0,0,0,.08); font-size: .62rem; opacity: .7; }
    .sticky-collabs { display: flex; align-items: center; gap: 2px; }
    .sticky-collab { width: 22px; height: 22px; border-radius: 50%; background: rgba(142,125,255,.3); color: #fff; font-size: .5rem; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; border: 2px solid var(--surface); }
    .sticky-actions { position: absolute; top: .5rem; right: .5rem; display: none; gap: .25rem; }
    .sticky:hover .sticky-actions { display: flex; }
    .sticky-actions .btn-xs { min-height: 30px; padding: .15rem .5rem; font-size: .62rem; border-radius: 6px; background: rgba(0,0,0,.15); color: inherit; border: none; backdrop-filter: blur(4px); }
    .sticky-actions .btn-xs:hover { background: rgba(0,0,0,.3); }
    .empty-state { text-align: center; padding: 4rem 2rem; color: var(--text-muted); }
    .empty-state .icon { font-size: 3rem; margin-bottom: 1rem; }
    .ql-editor { min-height: 200px; font-size: .9rem; }
    .ql-toolbar { border-radius: var(--radius-sm) var(--radius-sm) 0 0; }
    .ql-container { border-radius: 0 0 var(--radius-sm) var(--radius-sm); }
    .color-picker { display: flex; gap: .5rem; flex-wrap: wrap; }
    .color-swatch { width: 36px; height: 36px; border-radius: 50%; border: 2px solid transparent; cursor: pointer; transition: border-color .15s; }
    .color-swatch:hover, .color-swatch.active { border-color: var(--accent); }

    @media (max-width: 640px) {
        .container { padding: 1rem; }
        .notes-grid { grid-template-columns: 1fr; gap: 1rem; }
        .sticky { min-height: 160px; padding: 1rem; }
        .sticky-title { font-size: .85rem; }
        .sticky-preview { font-size: .72rem; }
        .sticky-footer { flex-direction: column; gap: .35rem; align-items: flex-start; }
        .ql-editor { min-height: 160px; font-size: .82rem; }
        .color-swatch { width: 32px; height: 32px; }
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="flex items-center justify-between mb-2" style="flex-wrap:wrap;gap:.75rem;">
        <div>
            <div class="page-title">📝 Notes</div>
            <div class="page-subtitle">Sticky notes with rich text ✦</div>
        </div>
        <button class="btn btn-primary" onclick="openNoteModal()">+ New Note</button>
    </div>

    @if (session('status'))
    <div class="flash">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('status') }} ✨
    </div>
    @endif

    @if($errors->any())
    <div class="alert">{{ $errors->first() }}</div>
    @endif

    @if ($notes->isEmpty())
    <div class="empty-state">
        <div class="icon">📌</div>
        <h3>No notes yet</h3>
        <p class="text-muted">Create your first sticky note to get started.</p>
        <button class="btn btn-primary" onclick="openNoteModal()" style="margin-top:1rem;">+ New Note</button>
    </div>
    @else
    <div class="notes-grid">
        @foreach ($notes as $note)
        <div class="sticky" style="background:{{ $note->color }};color:#1f1a3a;" onclick="openNoteModal({{ $note->id }})">
            <div class="sticky-actions" onclick="event.stopPropagation()">
                <button class="btn-xs" onclick="event.stopPropagation();deleteNote({{ $note->id }})">🗑</button>
            </div>
            <div class="sticky-title">{{ $note->title }}</div>
            <div class="sticky-preview">{!! $note->content ? Str::limit(strip_tags($note->content), 120) : '<em style="opacity:.5;">Empty note</em>' !!}</div>
            <div class="sticky-footer">
                <span>{{ $note->updated_at->diffForHumans() }}</span>
                <div class="sticky-collabs">
                    @php $collabs = $note->collaborators ?? collect(); @endphp
                    @foreach ($collabs->take(3) as $c)
                    <span class="sticky-collab" title="{{ $c->name }}">{{ strtoupper(substr($c->name, 0, 1)) }}</span>
                    @endforeach
                    @if ($collabs->count() > 3)
                    <span style="font-size:.55rem;opacity:.6;">+{{ $collabs->count()-3 }}</span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- Note Modal --}}
<div id="noteModal" class="modal-overlay">
    <div class="modal" style="max-width:680px;">
        <div class="modal-header">
            <span class="modal-title" id="noteModalTitle">✦ New Note</span>
            <button class="modal-close" onclick="closeNoteModal()">&times;</button>
        </div>
        <form id="noteForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="noteMethod" value="POST">
            <div class="modal-body">
                <div class="field">
                    <label>Title</label>
                    <input type="text" name="title" id="noteTitle" placeholder="Note title" required>
                </div>
                <div class="field">
                    <label>Content</label>
                    <div id="quillEditor" style="background:var(--surface2);border-radius:var(--radius-sm);color:var(--text);"></div>
                    <textarea name="content" id="noteContent" style="display:none;"></textarea>
                </div>
                <div class="field">
                    <label>Color</label>
                    <div class="color-picker">
                        @php $colors = ['#fff9c4','#ffccbc','#f8bbd0','#e1bee7','#c5cae9','#b3e5fc','#b2dfdb','#d7ccc8']; @endphp
                        @foreach ($colors as $c)
                        <div class="color-swatch {{ $loop->first ? 'active' : '' }}" style="background:{{ $c }};" data-color="{{ $c }}" onclick="selectColor(this)"></div>
                        @endforeach
                        <input type="hidden" name="color" id="noteColor" value="{{ $colors[0] }}">
                    </div>
                </div>
                <div class="field" id="collabSection" style="display:none;">
                    <label>Collaborators</label>
                    <div id="collabList" style="display:flex;flex-wrap:wrap;gap:.35rem;margin-bottom:.5rem;"></div>
                    <div class="flex gap-1">
                        <input type="email" id="inviteEmail" placeholder="email@example.com" style="flex:1;min-height:44px;padding:.5rem .8rem;border:1px solid var(--border);border-radius:var(--radius-sm);background:var(--surface2);color:var(--text);font-family:inherit;font-size:.82rem;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="inviteCollab()">Invite</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="padding:0 1.25rem 1.25rem;display:flex;flex-direction:column;gap:.55rem;">
                <button type="submit" class="btn btn-primary btn-full">Save Note ✦</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
let quill = null;
let currentNoteId = null;
const notesData = @json($notes);

function initQuill() {
    if (quill) return;
    try {
        quill = new Quill('#quillEditor', {
            theme: 'snow',
            placeholder: 'Write something...',
            modules: {
                toolbar: [
                    [{ 'header': [1,2,3,false] }],
                    ['bold','italic','underline','strike'],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    [{ 'color': [] }, { 'background': [] }],
                    ['link','clean']
                ]
            }
        });
    } catch (e) {
        console.error('Quill init failed', e);
    }
}

function openNoteModal(id) {
    const modal = document.getElementById('noteModal');
    const form = document.getElementById('noteForm');
    const method = document.getElementById('noteMethod');
    const title = document.getElementById('noteModalTitle');
    const noteTitle = document.getElementById('noteTitle');

    currentNoteId = id || null;

    // Show modal first so browser lays out the editor container
    modal.classList.add('active');

    // Defer Quill init + population to next frame so DOM is laid out
    requestAnimationFrame(function() {
        initQuill();

        if (id) {
            title.textContent = '✏️ Edit Note';
            method.value = 'PUT';
            form.action = '/notes/' + id;

            var note = null;
            for (var i = 0; i < notesData.length; i++) {
                if (notesData[i].id === id) { note = notesData[i]; break; }
            }

            if (note) {
                noteTitle.value = note.title;
                if (quill) quill.root.innerHTML = note.content || '';
                selectSwatch(note.color || '#fff9c4');
                document.getElementById('collabSection').style.display = 'block';
                renderCollabs(note.collaborators || []);
            }
        } else {
            title.textContent = '✦ New Note';
            method.value = 'POST';
            form.action = '/notes';
            noteTitle.value = '';
            if (quill) quill.root.innerHTML = '';
            selectSwatch('#fff9c4');
            document.getElementById('collabSection').style.display = 'none';
        }
    });
}

function closeNoteModal() {
    document.getElementById('noteModal').classList.remove('active');
}

document.getElementById('noteForm').addEventListener('submit', function(e) {
    if (quill) document.getElementById('noteContent').value = quill.root.innerHTML;
});

function selectColor(el) {
    document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('noteColor').value = el.dataset.color;
}

function selectSwatch(color) {
    document.querySelectorAll('.color-swatch').forEach(s => {
        s.classList.toggle('active', s.dataset.color === color);
    });
    document.getElementById('noteColor').value = color;
}

function renderCollabs(collabs) {
    const list = document.getElementById('collabList');
    if (!collabs.length) { list.innerHTML = '<span style="font-size:.72rem;color:var(--text-muted);">No collaborators</span>'; return; }
    list.innerHTML = collabs.map(c => 
        `<span class="badge badge-pending" style="font-size:.68rem;">${c.name} <span style="cursor:pointer;margin-left:.3rem;opacity:.6;" onclick="removeCollab(${c.id})">&times;</span></span>`
    ).join('');
}

function inviteCollab() {
    const email = document.getElementById('inviteEmail').value;
    if (!email || !currentNoteId) return;
    fetch('/notes/' + currentNoteId + '/invite', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('[name=_token]').value },
        body: JSON.stringify({ invited_email: email })
    }).then(r => r.json()).then(res => {
        if (res.success) {
            window.location.reload();
        } else {
            alert(res.message);
        }
    });
}

function removeCollab(userId) {
    if (!currentNoteId || !confirm('Remove this collaborator?')) return;
    fetch('/notes/' + currentNoteId + '/collaborators/' + userId + '/remove', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('[name=_token]').value }
    }).then(r => r.json()).then(res => {
        if (res.success) {
            window.location.reload();
        }
    });
}

function deleteNote(id) {
    if (!confirm('Delete this note?')) return;
    fetch('/notes/' + id, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('[name=_token]').value, 'X-HTTP-Method-Override': 'DELETE' }
    }).then(r => { window.location.reload(); });
}
</script>
@endsection
