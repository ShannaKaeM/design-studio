import { maybePromoteScalarValueIntoResponsive } from 'blocksy-customizer-sync'
import ctEvents from 'ct-events'

export const collectVariablesForRelatedSlideshow = () => ({
	woocommerce_related_products_slideshow_columns: [
		{
			selector: '.related [data-products], .upsells [data-products]',
			variable: 'grid-columns-width',
			responsive: true,
			extractValue: (val) => {
				const responsive = maybePromoteScalarValueIntoResponsive(val)

				ctEvents.trigger('blocksy:frontend:init')
				setTimeout(() => {
					const sliders = document.querySelectorAll(
						'.related .flexy-container, .upsells .flexy-container'
					)

					if (sliders.length) {
						sliders.forEach((slider) => {
							const firstChild = slider.querySelector(
								'.flexy-item:first-child'
							)

							if (slider.flexy) {
								slider.flexy.scheduleSliderRecalculation()
							}
						})
					}
				}, 50)

				return {
					desktop: `calc(100% / ${responsive.desktop})`,
					tablet: `calc(100% / ${responsive.tablet})`,
					mobile: `calc(100% / ${responsive.mobile})`,
				}
			},
		},
	],
})
