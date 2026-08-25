(() =>
{
    const dropZone = document.querySelector('.js-wrxtReferralUploadDrop')
    const input = document.querySelector('.js-wrxtReferralUploadInput')
    const preview = document.querySelector('.js-wrxtReferralUploadPreview')
    const fileName = document.querySelector('.js-wrxtReferralUploadName')

    if (!dropZone || !input)
    {
        return
    }

    const renderPreview = () =>
    {
        const file = input.files && input.files[0]
        if (!file)
        {
            if (preview)
            {
                preview.innerHTML = ''
            }
            if (fileName)
            {
                fileName.textContent = 'PNG, JPG, GIF veya WEBP'
            }
            return
        }

        if (fileName)
        {
            fileName.textContent = file.name
        }

        if (!preview || !file.type.startsWith('image/'))
        {
            return
        }

        const reader = new FileReader()
        reader.addEventListener('load', () =>
        {
            preview.innerHTML = ''
            const image = document.createElement('img')
            image.src = reader.result
            image.alt = ''
            image.style.maxWidth = '140px'
            image.style.maxHeight = '140px'
            image.style.borderRadius = '8px'
            image.style.display = 'block'
            preview.appendChild(image)
        })
        reader.readAsDataURL(file)
    }

    const setActive = state =>
    {
        dropZone.style.borderColor = state ? 'currentColor' : ''
        dropZone.style.opacity = state ? '0.8' : ''
    }

    ;['dragenter', 'dragover'].forEach(eventName =>
    {
        dropZone.addEventListener(eventName, event =>
        {
            event.preventDefault()
            event.stopPropagation()
            setActive(true)
        })
    })

    ;['dragleave', 'drop'].forEach(eventName =>
    {
        dropZone.addEventListener(eventName, event =>
        {
            event.preventDefault()
            event.stopPropagation()
            setActive(false)
        })
    })

    dropZone.addEventListener('drop', event =>
    {
        const files = event.dataTransfer && event.dataTransfer.files
        if (!files || !files.length)
        {
            return
        }

        const transfer = new DataTransfer()
        transfer.items.add(files[0])
        input.files = transfer.files
        renderPreview()
    })

    input.addEventListener('change', renderPreview)
})()
