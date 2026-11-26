/*
 * m4p_heroslider
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 */
/**
 * Slide form: when a video file is chosen, its first frame is drawn in the browser and attached to the
 * form as the poster file, so the photo shown until the video loads needs no separate upload and no
 * server tools. A photo chosen in the photo field still wins on the server.
 */
(function () {
  'use strict';

  function captureFirstFrame(file) {
    return new Promise(function (resolve, reject) {
      var url = URL.createObjectURL(file);
      var video = document.createElement('video');
      video.muted = true;
      video.playsInline = true;
      video.preload = 'auto';
      video.addEventListener('error', function () { URL.revokeObjectURL(url); reject(new Error('video')); });
      video.addEventListener('loadeddata', function () { video.currentTime = 0.05; });
      video.addEventListener('seeked', function () {
        var canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        URL.revokeObjectURL(url);
        canvas.toBlob(function (blob) { blob ? resolve(blob) : reject(new Error('frame')); }, 'image/jpeg', 0.9);
      }, { once: true });
      video.src = url;
    });
  }

  function attachPoster(form, blob) {
    var input = form.querySelector('input[name="poster"]');
    if (!input) {
      input = document.createElement('input');
      input.type = 'file';
      input.name = 'poster';
      input.hidden = true;
      form.appendChild(input);
    }
    var transfer = new DataTransfer();
    transfer.items.add(new File([blob], 'poster.jpg', { type: 'image/jpeg' }));
    input.files = transfer.files;
  }

  function showPreview(field, blob) {
    var preview = field.parentNode.querySelector('.m4p-hero-admin__frame');
    if (!preview) {
      preview = document.createElement('img');
      preview.className = 'm4p-hero-admin__preview m4p-hero-admin__frame';
      preview.alt = '';
      field.parentNode.appendChild(preview);
    }
    preview.src = URL.createObjectURL(blob);
  }

  document.addEventListener('change', function (event) {
    var field = event.target;
    if (field.name !== 'video' || !field.files || !field.files[0]) { return; }
    var form = field.form;
    captureFirstFrame(field.files[0]).then(function (blob) {
      attachPoster(form, blob);
      showPreview(field, blob);
    }).catch(function () { /* the browser cannot decode this file; the server keeps any photo given */ });
  });

  document.addEventListener('click', function (event) {
    var link = event.target.closest('[data-m4p-hero-confirm]');
    if (link && !window.confirm(link.getAttribute('data-m4p-hero-confirm'))) { event.preventDefault(); }
  });
})();
