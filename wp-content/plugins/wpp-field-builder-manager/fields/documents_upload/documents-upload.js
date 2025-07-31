jQuery(document).ready(function($) {
    // Обработка клика на кнопку "upload file"
    $(document).on('click', '.upload-link', function(e) {
        e.preventDefault(); // Предотвратить стандартное поведение, если это ссылка

        var $container = $(this).closest('.wpp-documents-upload-field');
        var loanId = $container.data('loan-id');
        var fieldName = $container.data('field-name');
        var allowedTypes = $container.data('allowed-types') || '';

        if (!loanId || !fieldName) {
            console.error('Missing loan ID or field name');
            return;
        }

        // Создаем скрытое поле загрузки файла
        var $inputField = $('<input type="file" style="display:none;" />');

        // Добавляем атрибуты к полю загрузки
        $inputField.attr({
            'data-field-name': fieldName,
            'data-loan-id': loanId
        });

        // Добавляем поле в DOM (лучше добавлять к body или контейнеру формы)
        $('body').append($inputField);

        // Активируем выбор файла
        $inputField.trigger('click');

        // Обработка изменения поля
        $inputField.on('change', function() {
            if (this.files.length === 0) {
                $inputField.remove();
                return;
            }

            var formData = new FormData();
            formData.append('action', 'wpp_upload_document');
            formData.append('loan_id', loanId);
            formData.append('field_name', fieldName);
            formData.append('file', this.files[0]);
            formData.append('nonce', wpp_ajax.nonce);
            if (allowedTypes) {
                formData.append('allowed_types', allowedTypes);
            }

            // Показываем индикатор загрузки
            var $missingDocs = $container.find('.missing-documents');
            var originalText = $missingDocs.length ? $missingDocs.text() : 'Uploading...';
            if ($missingDocs.length) {
                $missingDocs.text('Uploading...');
            } else {
                $container.find('.upload-link').text('Uploading...');
            }


            $.ajax({
                url: wpp_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        // Удаляем скрытое поле загрузки
                        $inputField.remove();

                        // Скрываем кнопку "upload file" и сообщение "missing documents"
                        $container.find('.upload-link').remove();
                        $container.find('.missing-documents').remove();

                        // Отображаем загруженный файл (используем имя файла из ответа)
                        var data = response.data;
                        var uploadedFileHtml = `
                            <a href="${data.url}" target="_blank">${data.name}</a>
                            <span class="date">${data.upload_date}</span>
                            <select class="status-select">
                                <option value="waiting_for_review">Waiting for Review</option>
                                <option value="reviewing">Reviewing</option>
                                <option value="changes_required">Changes Required</option>
                                <option value="rejected">Rejected</option>
                                <option value="accepted">Accepted</option>
                            </select>
                            <button type="button" class="remove-file" data-file="${data.name}" data-field="${fieldName}">✕</button>
                        `;
                        $container.prepend(uploadedFileHtml); // Добавляем в начало контейнера поля
                    } else {
                        alert('Upload failed: ' + (response.data || 'Unknown error'));
                        // Восстанавливаем текст
                        if ($missingDocs.length) {
                            $missingDocs.text(originalText);
                        } else {
                            $container.find('.upload-link').text(originalText);
                        }
                        $inputField.remove();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    alert('Upload failed: ' + error);
                    // Восстанавливаем текст
                    if ($missingDocs.length) {
                        $missingDocs.text(originalText);
                    } else {
                        $container.find('.upload-link').text(originalText);
                    }
                    $inputField.remove();
                }
            });
        });
    });

    // Обработка удаления файлов
    $(document).on('click', '.remove-file', function(e) {
        e.preventDefault();
        var $button = $(this);
        var filename = $button.data('file');
        var fieldName = $button.data('field');
        var $container = $button.closest('.wpp-documents-upload-field');
        var loanId = $container.data('loan-id');

        if (!loanId || !filename || !fieldName) {
            console.error('Missing data for deletion');
            return;
        }

        if (!confirm('Are you sure you want to delete this file?')) {
            return;
        }

        $.post(wpp_ajax.ajax_url, {
            action: 'wpp_delete_document',
            loan_id: loanId,
            field_name: fieldName,
            filename: filename,
            nonce: wpp_ajax.nonce
        }, function(response) {
            if (response.success) {
                // Удаляем элементы, связанные с файлом
                $button.parent().find('a, .date, .status-select, .remove-file').remove();

                // Восстанавливаем кнопку "upload file" и сообщение "missing documents"
                // Проверяем, не осталось ли других файлов
                if ($container.find('a').length === 0) {
                    $container.prepend('<span class="upload-link"><i class="fas fa-cloud-upload-alt"></i> upload file</span>');
                    $container.append('<span class="missing-documents"><i class="fas fa-exclamation-triangle"></i> missing documents</span>');
                }
            } else {
                alert('Delete failed: ' + (response.data || 'Unknown error'));
            }
        }).fail(function(xhr, status, error) {
            console.error('Delete AJAX Error:', xhr.responseText);
            alert('Delete failed: ' + error);
        });
    });
});
