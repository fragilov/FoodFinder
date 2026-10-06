/* ============================================================
   SHEET.JS — Bottom sheet mobile (3 nấc: peek, half, full)
   ============================================================ */

import { invalidateSize } from './map.js';

const STATES = ['peek', 'half', 'full'];
const THRESHOLDS = {
  peek: () => window.innerHeight - 112,
  half: () => window.innerHeight * 0.45,
  full: () => window.innerHeight * 0.08,
};

let sheetEl = null;
let handleEl = null;
let currentState = 'peek';
let startY = 0;
let startTranslateY = 0;
let isDragging = false;
let onStateChange = null;

/**
 * Khởi tạo bottom sheet
 */
export function initSheet(options = {}) {
  sheetEl = document.getElementById('sheet');
  handleEl = document.getElementById('sheet-handle');
  onStateChange = options.onStateChange || null;

  if (!sheetEl || !handleEl) return;

  // Touch events
  handleEl.addEventListener('touchstart', onTouchStart, { passive: true });
  document.addEventListener('touchmove', onTouchMove, { passive: false });
  document.addEventListener('touchend', onTouchEnd, { passive: true });

  // Click handle to cycle states
  handleEl.addEventListener('click', () => {
    const idx = STATES.indexOf(currentState);
    const next = STATES[(idx + 1) % STATES.length];
    setState(next);
  });

  // Apply initial state
  setState('peek', false);
}

/**
 * Đặt trạng thái sheet
 */
export function setState(state, animate = true) {
  if (!sheetEl) return;
  currentState = state;

  // Remove all state classes
  STATES.forEach(s => sheetEl.classList.remove(`sheet--${s}`));
  sheetEl.classList.add(`sheet--${state}`);

  if (!animate) {
    sheetEl.style.transition = 'none';
    requestAnimationFrame(() => {
      sheetEl.style.transition = '';
    });
  }

  invalidateSize();
  if (onStateChange) onStateChange(state);
}

/**
 * Lấy trạng thái hiện tại
 */
export function getState() {
  return currentState;
}

// ── Touch handlers ──

function onTouchStart(e) {
  if (!sheetEl) return;
  isDragging = true;
  startY = e.touches[0].clientY;
  const transform = getComputedStyle(sheetEl).transform;
  const matrix = new DOMMatrix(transform);
  startTranslateY = matrix.m42;

  sheetEl.style.transition = 'none';
}

function onTouchMove(e) {
  if (!isDragging || !sheetEl) return;

  const currentY = e.touches[0].clientY;
  const deltaY = currentY - startY;
  const newTranslateY = startTranslateY + deltaY;

  // Clamp: don't go above full state or below peek state
  const minY = THRESHOLDS.full();
  const maxY = THRESHOLDS.peek();
  const clampedY = Math.max(minY, Math.min(maxY, newTranslateY));

  sheetEl.style.transform = `translateY(${clampedY}px)`;

  // Prevent scrolling body while dragging sheet
  if (deltaY !== 0) e.preventDefault();
}

function onTouchEnd() {
  if (!isDragging || !sheetEl) return;
  isDragging = false;

  // Restore transition
  sheetEl.style.transition = '';
  sheetEl.style.transform = '';

  // Determine closest state based on current position
  const transform = getComputedStyle(sheetEl).transform;
  const matrix = new DOMMatrix(transform);
  const currentY = matrix.m42;

  const peekY = THRESHOLDS.peek();
  const halfY = THRESHOLDS.half();
  const fullY = THRESHOLDS.full();

  // Find closest
  const distances = {
    peek: Math.abs(currentY - peekY),
    half: Math.abs(currentY - halfY),
    full: Math.abs(currentY - fullY),
  };

  // Also consider direction of drag
  const deltaFromStart = currentY - startTranslateY;

  let target;
  if (Math.abs(deltaFromStart) > 60) {
    // Significant drag — go in drag direction
    if (deltaFromStart > 0) {
      // Dragged down
      const currentIdx = STATES.indexOf(currentState);
      target = STATES[Math.max(0, currentIdx - 1)];
    } else {
      // Dragged up
      const currentIdx = STATES.indexOf(currentState);
      target = STATES[Math.min(STATES.length - 1, currentIdx + 1)];
    }
  } else {
    // Small drag — snap to closest
    target = Object.entries(distances).sort((a, b) => a[1] - b[1])[0][0];
  }

  setState(target);
}
