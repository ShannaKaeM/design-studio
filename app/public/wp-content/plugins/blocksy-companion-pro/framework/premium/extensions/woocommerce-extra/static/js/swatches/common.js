import { handleOutofStock } from './out-of-stock'

export const computeSwatch = (el, args = {}) => {
	args = {
		computeArchiveUrl: false,
		...args,
	}
	;[...el.querySelectorAll('.ct-variation-swatches')].map((swatchesEl) => {
		const select = swatchesEl.querySelector('select')

		if (!select) {
			return
		}

		const maybeActive = swatchesEl.querySelector('.active')

		if (maybeActive) {
			maybeActive.classList.remove('active')

			const labelContainer = document.querySelector(
				`[for="${select.dataset.attribute_name.replace(
					'attribute_',
					''
				)}"]`
			)

			if (labelContainer) {
				labelContainer.textContent =
					labelContainer.textContent.split(':')?.[0].trim() ||
					labelContainer.textContent
			}
		}

		const selectValue = JSON.stringify(String(select.value))

		if (
			selectValue &&
			swatchesEl.querySelector(`[data-value=${selectValue}]`)
		) {
			const label = select.querySelector(
				`[value=${selectValue}]`
			).textContent

			const labelContainer = document.querySelector(
				`[for="${select.dataset.attribute_name.replace(
					'attribute_',
					''
				)}"]`
			)

			if (labelContainer) {
				labelContainer.textContent = `${
					labelContainer.textContent.split(':')?.[0].trim() ||
					labelContainer.textContent
				}: ${label}`
			}

			swatchesEl
				.querySelector(`[data-value=${selectValue}]`)
				.classList.add('active')
		}

		handleOutofStock(el)
		select.dispatchEvent(new Event('change'))

		let urlForButton = null

		const maybeArchiveButton = el
			.closest('.product')
			.querySelector('.add_to_cart_button')

		if (args.computeArchiveUrl && maybeArchiveButton) {
			urlForButton = new URL(maybeArchiveButton.href)

			urlForButton.searchParams.delete(select.name)
		}

		;[...swatchesEl.querySelectorAll('[data-value]')].map(
			(singleSwatchEl) => {
				singleSwatchEl.classList.remove('active')

				const swatchValue = JSON.stringify(
					String(singleSwatchEl.dataset.value)
				)

				if (swatchValue === selectValue) {
					singleSwatchEl.classList.add('active')

					if (urlForButton) {
						urlForButton.searchParams.set(
							select.name,
							JSON.parse(selectValue)
						)
					}
				}
			}
		)

		if (urlForButton && maybeArchiveButton) {
			maybeArchiveButton.href = urlForButton.toString()
		}
	})
}
