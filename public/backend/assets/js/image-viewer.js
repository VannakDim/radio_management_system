
let isDragging = false;
let startX, startY, scrollLeft, scrollTop;

function openImageModal(imageUrl) {
    const modalImage = document.getElementById('modalImage');
    modalImage.src = imageUrl;
    modalImage.style.transform = 'scale(1)';
    modalImage.style.cursor = 'zoom-in';
    document.getElementById('imageModal').style.display = 'block';
}

function closeImageModal() {
    document.getElementById('imageModal').style.display = 'none';
}

function zoomImage(img) {
    if (img.style.cursor === 'zoom-in') {
        img.style.cursor = 'move';
        img.style.transform = 'scale(2)';
        img.addEventListener('mousedown', startDragging);
        img.addEventListener('mouseup', stopDragging);
        img.addEventListener('mousemove', dragImage);
    } else {
        img.style.cursor = 'zoom-in';
        img.style.transform = 'scale(1)';
        img.removeEventListener('mousedown', startDragging);
        img.removeEventListener('mouseup', stopDragging);
        img.removeEventListener('mousemove', dragImage);
    }
}

function startDragging(e) {
    isDragging = true;
    startX = e.clientX - this.offsetLeft;
    startY = e.clientY - this.offsetTop;
    scrollLeft = this.parentElement.scrollLeft;
    scrollTop = this.parentElement.scrollTop;
}

function stopDragging() {
    isDragging = false;
}

function dragImage(e) {
    if (!isDragging) return;
    e.preventDefault();
    const x = e.clientX - startX;
    const y = e.clientY - startY;
    const walkX = x * 2;
    const walkY = y * 2;
    this.parentElement.scrollLeft = scrollLeft - walkX;
    this.parentElement.scrollTop = scrollTop - walkY;
}

window.onclick = function (event) {
    const modal = document.getElementById('imageModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}