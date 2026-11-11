//
import { config } from '@hotwired/turbo';

// Keep Turbo's progress bar from introducing a new visual element.
config.drive.progressBarDelay = 2147483647;

document.addEventListener('turbo:load', () => {
    ['create', 'edit'].forEach((formType) => {
        const workType = document.getElementById(`work-type-${formType}`);
        const unit = document.getElementById(`unit-${formType}`);

        if (!workType || !unit) {
            return;
        }

        const selectedUnit = workType.selectedOptions[0]?.dataset.unit;

        unit.value = selectedUnit ?? '';
    });
});

document.addEventListener('change', (event) => {
    const target = event.target;

    if (!(target instanceof HTMLSelectElement)) {
        if (!(target instanceof HTMLInputElement) || !['photo-input-create', 'photo-input-edit'].includes(target.id)) {
            return;
        }

        const previewId = target.id === 'photo-input-create' ? 'photo-preview-create' : 'photo-preview-edit';
        const preview = document.getElementById(previewId);

        if (!preview) {
            return;
        }

        preview.replaceChildren();

        Array.from(target.files ?? []).forEach((file) => {
            if (!file.type.startsWith('image/')) {
                return;
            }

            const reader = new FileReader();
            reader.onload = (loadEvent) => {
                const image = document.createElement('img');
                image.src = loadEvent.target?.result;
                image.alt = 'معاينة الصورة';
                image.style.width = '70px';
                image.style.height = '70px';
                image.style.objectFit = 'cover';
                image.style.borderRadius = '8px';
                image.style.border = '1px solid #dfe3eb';
                preview.appendChild(image);
            };

            reader.readAsDataURL(file);
        });

        return;
    }

    if (!['work-type-create', 'work-type-edit'].includes(target.id)) {
        return;
    }

    const unitId = target.id === 'work-type-create' ? 'unit-create' : 'unit-edit';
    const unit = document.getElementById(unitId);
    const selectedUnit = target.selectedOptions[0]?.dataset.unit;

    if (unit) {
        unit.value = selectedUnit ?? '';
    }
});

document.addEventListener('click', (event) => {
    const target = event.target;

    if (!(target instanceof Element) || !target.closest('#add-member-btn')) {
        return;
    }

    const wrapper = document.getElementById('members-wrapper');

    if (!wrapper) {
        return;
    }

    const input = document.createElement('input');
    input.type = 'text';
    input.name = 'supervisory_members[]';
    input.className = 'form-control mb-2';
    input.placeholder = 'اسم عضو الإشراف';
    wrapper.appendChild(input);
});
