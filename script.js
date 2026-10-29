const note = document.querySelector('#welcome-note');
document.querySelector('#note-button').addEventListener('click', () => note.showModal());
document.querySelector('#close-note').addEventListener('click', () => note.close());
note.addEventListener('close', () => document.querySelector('#note-button').focus({ preventScroll: true }));
note.addEventListener('click', (event) => {
  if (event.target !== note) return;
  const rect = note.getBoundingClientRect();
  if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) note.close();
});
document.querySelector('#year').textContent = new Date().getFullYear();
