<?php $__env->startSection('title', 'XML — '.$char->name); ?>
<?php $__env->startSection('breadcrumb', 'Characters / '.$char->name.' / XML Editor'); ?>

<?php $__env->startSection('content'); ?>


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/dracula.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/search/searchcursor.min.js"></script>

<style>
/* ---------- editor shell ---------- */
.xml-editor-wrap {
    border: 2px solid #4a5059;
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,.5);
    display: flex;
    flex-direction: column;
    height: calc(100vh - 148px); /* fill viewport, no page scroll */
}
/* ---------- toolbar ---------- */
#xml-toolbar {
    background: #21252b;
    border-bottom: 1px solid #4a5059;
    padding: 5px 10px;
    display: flex;
    align-items: center;
    gap: 7px;
    flex-wrap: nowrap;
    flex-shrink: 0;
}
#xml-toolbar input[type=text] {
    background: #1a1d23;
    border: 1px solid #555;
    color: #abb2bf;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-family: monospace;
    width: 220px;
    outline: none;
}
#xml-toolbar input[type=text]:focus { border-color: #61afef; }
#xml-toolbar button {
    background: #2c313a;
    border: 1px solid #555;
    color: #abb2bf;
    padding: 3px 9px;
    border-radius: 4px;
    font-size: 11px;
    cursor: pointer;
    white-space: nowrap;
    flex-shrink: 0;
}
#xml-toolbar button:hover { background: #3e4451; color: #fff; }
#xml-toolbar .btn-save { background:#2d4a2d; border-color:#4a7a4a; color:#98c379; }
#xml-toolbar .btn-save:hover { background:#3a603a; color:#b5e890; }
#xml-toolbar .btn-cancel { background:#3a2a2a; border-color:#6a3a3a; color:#e06c75; }
#xml-toolbar .btn-cancel:hover { background:#4a3030; }
#xml-toolbar .info { color:#636d83; font-size:11px; font-family:monospace; flex-shrink:0; }
#xml-toolbar .match-info { color:#e5c07b; font-size:11px; font-family:monospace; min-width:70px; }
.toolbar-sep { color:#444; flex-shrink:0; }
/* ---------- view mode ---------- */
#xml-wrap {
    display: flex;
    font-family: 'Consolas','Monaco','Courier New',monospace;
    font-size: 12px;
    line-height: 1.6;
    background: #282c34;
    flex: 1;
    overflow: hidden;
}
#ln-col {
    min-width: 46px;
    padding: 12px 0;
    text-align: right;
    color: #636d83;
    background: #21252b;
    user-select: none;
    border-right: 1px solid #3a3f4b;
    flex-shrink: 0;
    overflow: hidden;
}
#ln-col span { display:block; padding-right:8px; line-height:1.6; }
#code-col { flex:1; overflow:auto; padding:12px 16px; }
#code-col pre { margin:0; background:transparent !important; padding:0 !important; white-space:pre-wrap; word-break:break-all; }
#code-col code { background:transparent !important; font-size:12px !important; line-height:1.6 !important; }
mark.hl { background:#e5c07b; color:#282c34; border-radius:2px; padding:0 1px; }
mark.hl-current { background:#e06c75; color:#fff; }
/* ---------- edit mode (CodeMirror) ---------- */
#xml-edit-wrap { display:none; flex:1; flex-direction:column; overflow:hidden; }
#cm-host { flex:1; overflow:hidden; }
.CodeMirror {
    font-family: 'Consolas','Monaco','Courier New',monospace !important;
    font-size: 12px !important;
    line-height: 1.6 !important;
}
.CodeMirror-scroll { overflow: auto !important; }
.CodeMirror-wrap pre { word-break: break-all; }
/* CM search highlight */
.cm-match-hl { background: rgba(229,192,123,0.4); border-radius: 2px; }
</style>

<?php echo $__env->make('panel._flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


<div class="pw-card mb-2">
    <div class="pw-card-body !py-2 flex flex-wrap items-center gap-3">
        <span class="text-[var(--color-accent)]">◉</span>
        <span class="text-[13px] font-bold"><?php echo e($char->name); ?></span>
        <span class="text-[11px] text-[var(--color-text-muted)]">#<?php echo e($char->id); ?></span>
        <span class="text-[11px] text-[var(--color-text-muted)]">·</span>
        <span class="text-[11px] text-[var(--color-text-muted)]">Owner: <strong class="text-[var(--color-text)]"><?php echo e($char->username ?? '(deleted)'); ?></strong></span>
        <span class="text-[10px] text-[var(--color-text-muted)]">v<strong class="text-[var(--color-text)] font-mono"><?php echo e($version); ?></strong></span>
        <div class="ml-auto">
            <a href="<?php echo e(route('panel.characters.show', $char->id)); ?>" class="pw-btn pw-btn-sm">← Back</a>
        </div>
    </div>
</div>


<div class="xml-editor-wrap">

    
    <div id="xml-toolbar">
        <span class="info" id="toolbar-mode-label">View Mode</span>
        <span class="toolbar-sep">|</span>

        <div id="search-group" style="display:flex;gap:5px;align-items:center;">
            <input type="text" id="search-input" placeholder="Cari: extraprop, level, realm_data ..."
                   oninput="onSearchInput(this.value)" onkeydown="handleKey(event)">
            <button onclick="doPrevMatch()">▲</button>
            <button onclick="doNextMatch()">▼</button>
            <button onclick="doClearSearch()">✕</button>
            <span class="match-info" id="match-info"></span>
        </div>

        <div id="edit-buttons" style="display:none;gap:5px;align-items:center;">
            <button class="btn-save" onclick="submitSave()">✓ Save XML</button>
            <button class="btn-cancel" onclick="cancelEdit()">✕ Cancel</button>
        </div>

        <span style="margin-left:auto;display:flex;gap:5px;">
            <button onclick="copyXml()" id="btn-copy">⎘ Copy</button>
            <button onclick="toggleWrap()" id="btn-wrap">⇌ No-wrap</button>
            <button onclick="toggleEdit()" id="btn-edit" style="background:#1e3a5f;border-color:#3a6ea0;color:#61afef;">✎ Edit</button>
        </span>
    </div>

    
    <div id="xml-wrap">
        <div id="ln-col"></div>
        <div id="code-col">
            <pre><code id="xml-code" class="language-xml"></code></pre>
        </div>
    </div>

    
    <div id="xml-edit-wrap">
        <form id="xml-save-form" method="POST" action="<?php echo e(route('panel.characters.xml.save', $char->id)); ?>">
            <?php echo csrf_field(); ?>
            <textarea id="xml-textarea" name="xml" style="display:none"></textarea>
        </form>
        <div id="cm-host"></div>
    </div>

</div>

<script>
const RAW_XML = <?php echo json_encode($xmlString, 15, 512) ?>;
// view-mode search state
let currentMatches = [];
let currentIdx = -1;
let wrapMode = true; // wrap on by default
let editMode = false;
let cmEditor = null;
// edit-mode (CM) search state
let cmMarkers = [];
let cmMatches = [];
let cmMatchIdx = -1;

// ---- View mode ----
function buildView(xmlStr) {
    const highlighted = hljs.highlight(xmlStr, {language: 'xml'}).value;
    document.getElementById('xml-code').innerHTML = highlighted;
    const lines = xmlStr.split('\n');
    const lnCol = document.getElementById('ln-col');
    lnCol.innerHTML = '';
    lines.forEach((_, i) => {
        const s = document.createElement('span');
        s.textContent = i + 1;
        lnCol.appendChild(s);
    });
}

// ---- View-mode search ----
function doSearch(q) {
    currentMatches = [];
    currentIdx = -1;
    const base = hljs.highlight(RAW_XML, {language: 'xml'}).value;
    if (!q.trim()) {
        document.getElementById('xml-code').innerHTML = base;
        document.getElementById('match-info').textContent = '';
        return;
    }
    const esc = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const regex = new RegExp(esc, 'gi');
    let m;
    while ((m = regex.exec(RAW_XML)) !== null) currentMatches.push(m.index);
    if (!currentMatches.length) {
        document.getElementById('match-info').textContent = 'No matches';
        document.getElementById('xml-code').innerHTML = base;
        return;
    }
    const marked = RAW_XML.replace(regex, x => `\x00${x}\x01`);
    document.getElementById('xml-code').innerHTML = hljs.highlight(marked, {language: 'xml'}).value
        .replace(/\x00/g, '<mark class="hl">').replace(/\x01/g, '</mark>');
    document.getElementById('match-info').textContent = `${currentMatches.length} match${currentMatches.length>1?'es':''}`;
    currentIdx = 0;
    scrollToMatch(0);
}
function scrollToMatch(idx) {
    const marks = document.querySelectorAll('#xml-code mark.hl');
    marks.forEach(el => el.classList.remove('hl-current'));
    if (marks[idx]) {
        marks[idx].classList.add('hl-current');
        marks[idx].scrollIntoView({block:'center', behavior:'smooth'});
        document.getElementById('match-info').textContent = `${idx+1}/${marks.length}`;
    }
}

// ---- Edit-mode (CodeMirror) search ----
function cmClearMarkers() {
    cmMarkers.forEach(mk => mk.clear());
    cmMarkers = [];
}
function cmDoSearch(q) {
    cmClearMarkers();
    cmMatches = [];
    cmMatchIdx = -1;
    document.getElementById('match-info').textContent = '';
    if (!q.trim() || !cmEditor) return;
    const cur = cmEditor.getSearchCursor(q, null, {caseFold: true});
    while (cur.findNext()) {
        const from = cur.from(), to = cur.to();
        cmMatches.push({from, to});
        cmMarkers.push(cmEditor.markText(from, to, {className: 'cm-match-hl'}));
    }
    if (!cmMatches.length) {
        document.getElementById('match-info').textContent = 'No matches';
        return;
    }
    cmMatchIdx = 0;
    cmJumpTo(0);
}
function cmJumpTo(idx) {
    if (!cmMatches.length) return;
    const match = cmMatches[idx];
    cmEditor.scrollIntoView({from: match.from, to: match.to}, 80);
    cmEditor.setSelection(match.from, match.to);
    document.getElementById('match-info').textContent = `${idx+1}/${cmMatches.length}`;
}
function cmNextMatch()  { if (!cmMatches.length) return; cmMatchIdx=(cmMatchIdx+1)%cmMatches.length; cmJumpTo(cmMatchIdx); }
function cmPrevMatch()  { if (!cmMatches.length) return; cmMatchIdx=(cmMatchIdx-1+cmMatches.length)%cmMatches.length; cmJumpTo(cmMatchIdx); }
function cmClearSearch(){ cmClearMarkers(); cmMatches=[]; cmMatchIdx=-1; document.getElementById('match-info').textContent=''; }

// ---- Unified dispatch ----
function onSearchInput(q) { editMode ? cmDoSearch(q) : doSearch(q); }
function doNextMatch()  { editMode ? cmNextMatch() : (currentMatches.length && (currentIdx=(currentIdx+1)%currentMatches.length, scrollToMatch(currentIdx))); }
function doPrevMatch()  { editMode ? cmPrevMatch() : (currentMatches.length && (currentIdx=(currentIdx-1+currentMatches.length)%currentMatches.length, scrollToMatch(currentIdx))); }
function doClearSearch(){ document.getElementById('search-input').value=''; editMode ? cmClearSearch() : doSearch(''); }
function handleKey(e) {
    if (e.key==='Enter') { e.shiftKey ? doPrevMatch() : doNextMatch(); }
    if (e.key==='Escape') doClearSearch();
}

// ---- Actions ----
function copyXml() {
    const text = cmEditor ? cmEditor.getValue() : RAW_XML;
    navigator.clipboard.writeText(text).then(() => {
        const btn = document.getElementById('btn-copy');
        const o = btn.textContent;
        btn.textContent = '✓ Copied!';
        setTimeout(() => btn.textContent = o, 1500);
    });
}
function toggleWrap() {
    wrapMode = !wrapMode;
    const pre = document.getElementById('code-col').querySelector('pre');
    pre.style.whiteSpace = wrapMode ? 'pre-wrap' : 'pre';
    pre.style.wordBreak  = wrapMode ? 'break-all' : 'normal';
    document.getElementById('btn-wrap').textContent = wrapMode ? '⇌ No-wrap' : '⇌ Wrap';
    if (cmEditor) cmEditor.setOption('lineWrapping', wrapMode);
}

// ---- Edit mode ----
function toggleEdit() {
    editMode = true;
    document.getElementById('xml-wrap').style.display = 'none';
    document.getElementById('xml-edit-wrap').style.display = 'flex';
    document.getElementById('edit-buttons').style.display = 'flex';
    document.getElementById('btn-edit').style.display = 'none';
    document.getElementById('btn-wrap').style.display = 'none';
    document.getElementById('toolbar-mode-label').textContent = 'Edit Mode';
    document.getElementById('toolbar-mode-label').style.color = '#98c379';

    // Wait for flex layout then measure real height
    requestAnimationFrame(() => requestAnimationFrame(() => {
        const wrapH = document.querySelector('.xml-editor-wrap').offsetHeight;
        const toolH = document.getElementById('xml-toolbar').offsetHeight;
        const editorH = wrapH - toolH;

        if (!cmEditor) {
            cmEditor = CodeMirror(document.getElementById('cm-host'), {
                value: RAW_XML,
                mode: 'xml',
                theme: 'dracula',
                lineNumbers: true,
                lineWrapping: true,
                indentUnit: 2,
                tabSize: 2,
                autofocus: true,
            });
        } else {
            cmEditor.setValue(RAW_XML);
        }
        cmEditor.setSize(null, editorH);
        cmEditor.refresh();

        // Re-run search if query exists
        const q = document.getElementById('search-input').value;
        if (q.trim()) cmDoSearch(q);
    }));
}

function cancelEdit() {
    editMode = false;
    cmClearSearch();
    document.getElementById('xml-wrap').style.display = 'flex';
    document.getElementById('xml-edit-wrap').style.display = 'none';
    document.getElementById('edit-buttons').style.display = 'none';
    document.getElementById('btn-edit').style.display = '';
    document.getElementById('btn-wrap').style.display = '';
    document.getElementById('toolbar-mode-label').textContent = 'View Mode';
    document.getElementById('toolbar-mode-label').style.color = '';
    // Re-run view search if query exists
    const q = document.getElementById('search-input').value;
    if (q.trim()) doSearch(q);
}

function submitSave() {
    if (!confirm('Simpan perubahan XML ke karakter ini?')) return;
    // Copy CodeMirror value → hidden textarea → submit
    document.getElementById('xml-textarea').value = cmEditor.getValue();
    document.getElementById('xml-save-form').submit();
}

// Init
buildView(RAW_XML);
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('panel.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/pw-panel/resources/views/panel/character/xml.blade.php ENDPATH**/ ?>