// Admin panel
export function adminShortcut() {
  window.addEventListener('keypress', e => e.shiftKey && e.keyCode === 88 && (window.location.href = '/admin'), false);
}

// @deprecated
export {
  onDomReady, once, template,
  swipeable, observable,
} from 'cantil';
