document.addEventListener('DOMContentLoaded', () => {
  // simple client-side filter for search (optional, server also filters)
  const searchInput = document.querySelector('#search');
  const lists = document.querySelectorAll('#user-available li, #admin-inventory li');

  if (searchInput && lists.length) {
    searchInput.addEventListener('input', () => {
      const term = searchInput.value.toLowerCase();
      lists.forEach(li => {
        const text = li.textContent.toLowerCase();
        li.style.display = text.includes(term) ? '' : 'none';
      });
    });
  }

  // Show selected file name in file upload
  const fileInput = document.querySelector('#image-upload');
  const fileLabel = document.querySelector('.file-label span');
  
  if (fileInput && fileLabel) {
    fileInput.addEventListener('change', (e) => {
      const fileName = e.target.files[0]?.name;
      if (fileName) {
        fileLabel.textContent = `Selected: ${fileName}`;
      } else {
        fileLabel.textContent = 'Choose Image (Optional)';
      }
    });
  }
});