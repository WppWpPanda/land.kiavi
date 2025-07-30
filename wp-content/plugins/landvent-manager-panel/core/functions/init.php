<?php

defined( 'ABSPATH' ) || exit;

require_once 'get-colums-data.php';
require_once 'save-loans.php';
require_once 'helpers.php';

function debugPanel(...$data)
{
	echo <<<HTML
<style>
#debug-panel {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 250px;
    background: #1e1e1e;
    color: #f8f8f2;
    font-family: monospace;
    z-index: 99999;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

#debug-panel.collapsed {
    display: none;
}

#debug-panel-header {
    background: #2d2d2d;
    padding: 5px 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    user-select: none;
    cursor: pointer;
    font-size: 14px;
}

#debug-panel-title {
    flex-grow: 1;
}

#debug-panel-buttons button {
    background: none;
    border: none;
    color: #ccc;
    margin-left: 10px;
    cursor: pointer;
    font-size: 16px;
}

#debug-panel-buttons button:hover {
    color: white;
}

#debug-panel-content {
    flex: 1;
    padding: 10px;
    white-space: pre-wrap;
    word-wrap: break-word;
    overflow-y: auto;
    background: #2d2d2d;
}

.resizer {
    height: 5px;
    background: #444;
    cursor: ns-resize;
    position: relative;
    z-index: 100000;
}
.resizer::after {
    content: '↕';
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    color: #888;
    font-size: 12px;
    opacity: 0.5;
}
</style>

<div id="debug-panel">
    <div id="debug-panel-header" onclick="toggleDebugPanel()">
        <span id="debug-panel-title">▶ Debug Panel</span>
        <div id="debug-panel-buttons">
            <button onclick="hideDebugPanel(event)">✖</button>
            <button id="toggle-btn">▼</button>
        </div>
    </div>
    <div id="debug-panel-content">
HTML;

	foreach ($data as $item) {
		ob_start();
		var_dump($item);
		$output = htmlspecialchars(ob_get_clean());
		echo "<pre>$output</pre>";
	}

	echo <<<HTML
    </div>
    <div class="resizer" id="panel-resizer"></div>
</div>

<script>
const panel = document.getElementById('debug-panel');
const resizer = document.getElementById('panel-resizer');
const toggleBtn = document.getElementById('toggle-btn');

document.addEventListener("DOMContentLoaded", function() {
    const state = localStorage.getItem('debugPanelState');
    const height = localStorage.getItem('debugPanelHeight') || '250';

    if (state === 'hidden') {
        panel.style.display = 'none';
    } else if (state === 'collapsed') {
        panel.classList.add('collapsed');
        toggleBtn.textContent = '▶';
    } else {
        panel.classList.remove('collapsed');
        toggleBtn.textContent = '▼';
    }
    panel.style.height = height + 'px';
});

let isResizing = false;

resizer.addEventListener('mousedown', function(e) {
    isResizing = true;
    document.body.style.cursor = 'ns-resize';
    e.preventDefault();
});

document.addEventListener('mousemove', function(e) {
    if (!isResizing) return;

    // Вычисляем новую высоту на основе положения курсора
    const newHeight = window.innerHeight - e.clientY;
    const minHeight = 100;
    const maxHeight = window.innerHeight - 50;

    if (newHeight >= minHeight && newHeight <= maxHeight) {
        panel.style.height = newHeight + 'px';
        localStorage.setItem('debugPanelHeight', newHeight);
    }
});

document.addEventListener('mouseup', function() {
    isResizing = false;
    document.body.style.cursor = '';
});

function toggleDebugPanel() {
    const isCollapsed = panel.classList.contains('collapsed');
    if (isCollapsed) {
        panel.classList.remove('collapsed');
        toggleBtn.textContent = '▼';
    } else {
        panel.classList.add('collapsed');
        toggleBtn.textContent = '▶';
    }
    saveState();
}

function hideDebugPanel(e) {
    e.stopPropagation();
    panel.style.display = 'none';
    localStorage.setItem('debugPanelState', 'hidden');
}

function saveState() {
    if (panel.style.display === 'none') {
        localStorage.setItem('debugPanelState', 'hidden');
    } else if (panel.classList.contains('collapsed')) {
        localStorage.setItem('debugPanelState', 'collapsed');
    } else {
        localStorage.setItem('debugPanelState', 'expanded');
    }
}

window.showDebugPanel = function() {
    panel.style.display = 'block';
    panel.classList.remove('collapsed');
    toggleBtn.textContent = '▼';
    localStorage.removeItem('debugPanelState');
    saveState();
};
</script>

<!-- Кнопка показа -->
<button onclick="showDebugPanel()" style="position: fixed; bottom: 20px; right: 20px; z-index: 99999; background: #444; color: #fff; border: none; padding: 10px 15px; cursor: pointer;">Показать Debug Panel</button>
HTML;
}


add_action('init', function() {
remove_action( 'wpp_lmp_loan_content','wpp_term_additional_reserve',40);
//remove_action( 'wpp_lmp_loan_content','wpp_term_fees',50);
remove_action( 'wpp_lmp_loan_content','wpp_term_milestones',60);
remove_action( 'wpp_lmp_loan_content','wpp_term_payments',70);
remove_action( 'wpp_lmp_loan_content','wpp_term_conditions',80);
remove_action( 'wpp_lmp_loan_content','wpp_term_investors',90);
remove_action( 'wpp_lmp_loan_content','wpp_term_attorney',100);
remove_action( 'wpp_lmp_loan_content','wpp_term_title_company',110);
remove_action( 'wpp_lmp_loan_content' ,'wpp_term_required_documents',120);
remove_action( 'wpp_lmp_loan_content','wpp_term_required_documents',130);
},50);
