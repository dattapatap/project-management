window.csdApi = {
    csrf() {
        return $('meta[name="csrf-token"]').attr('content');
    },
    /** Normalize jQuery .serialize() string or plain object for $.ajax */
    payload(data) {
        const token = this.csrf();
        if (typeof data === 'string') {
            const body = data ? data + '&_token=' + encodeURIComponent(token) : '_token=' + encodeURIComponent(token);
            return { body, contentType: 'application/x-www-form-urlencoded; charset=UTF-8' };
        }
        return { body: { ...data, _token: token }, contentType: undefined };
    },
    mountModals() {
        $('.csd-modal').each(function () {
            const $modal = $(this);
            if ($modal.parent()[0] !== document.body) {
                $modal.appendTo('body');
            }
        });
    },
    cleanupModalBackdrop() {
        $('body').removeClass('modal-open').css('padding-right', '');
        $('.modal-backdrop').remove();
    },
    put(url, data, done) {
        const token = this.csrf();
        let ajaxData;
        let contentType;
        if (typeof data === 'string') {
            ajaxData = (data ? data + '&' : '') + '_token=' + encodeURIComponent(token) + '&_method=PUT';
            contentType = 'application/x-www-form-urlencoded; charset=UTF-8';
        } else {
            ajaxData = { ...data, _token: token, _method: 'PUT' };
        }
        $.ajax({
            url,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token },
            contentType: contentType,
            data: ajaxData,
            success(res) {
                if (res.success) {
                    if (window.alertify) {
                        alertify.success(res.message);
                    }
                    if (typeof done === 'function') {
                        done(res);
                    }
                } else if (window.alertify) {
                    alertify.error(res.message || 'Request failed');
                }
            },
            error(xhr) {
                const msg = xhr.responseJSON?.message
                    || (xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join(' ') : null)
                    || 'Request failed';
                if (window.alertify) {
                    alertify.error(msg);
                }
            },
        });
    },
    post(url, data, done) {
        const token = this.csrf();
        const { body, contentType } = this.payload(data);
        $.ajax({
            url,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token },
            contentType: contentType,
            data: body,
            success(res) {
                if (res.success) {
                    if (window.alertify) {
                        alertify.success(res.message);
                    }
                    if (typeof done === 'function') {
                        done(res);
                    }
                } else if (window.alertify) {
                    alertify.error(res.message || 'Request failed');
                }
            },
            error(xhr) {
                const msg = xhr.responseJSON?.message
                    || (xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join(' ') : null)
                    || 'Request failed';
                if (window.alertify) {
                    alertify.error(msg);
                }
            },
        });
    },
    get(url, done) {
        $.get(url, done).fail(function (xhr) {
            if (window.alertify) {
                alertify.error(xhr.responseJSON?.message || 'Could not load data');
            }
        });
    },
    closeModal(modalSelector, formSelector) {
        const $modal = $(modalSelector);
        if (!$modal.length) {
            return;
        }

        if (formSelector) {
            const $form = $(formSelector);
            if ($form.length && $form[0]) {
                $form[0].reset();
            }
        }

        $modal.modal('hide');

        $modal.one('hidden.bs.modal', function () {
            csdApi.cleanupModalBackdrop();
        });

        // Fallback when Bootstrap hide does not fire hidden (nested layout / overflow)
        setTimeout(function () {
            if (!$modal.hasClass('show')) {
                csdApi.cleanupModalBackdrop();
            }
        }, 350);
    },
};

$(function () {
    csdApi.mountModals();

    $(document).on('click', '.csd-modal [data-dismiss="modal"]', function (e) {
        e.preventDefault();
        const $modal = $(this).closest('.modal');
        const $form = $modal.find('form').first();
        const formSel = $form.length && $form.attr('id') ? '#' + $form.attr('id') : null;
        csdApi.closeModal('#' + $modal.attr('id'), formSel);
    });
});
