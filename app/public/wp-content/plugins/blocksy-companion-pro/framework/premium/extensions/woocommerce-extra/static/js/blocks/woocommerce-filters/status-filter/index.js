import { createElement, useState, useEffect } from '@wordpress/element'

import { __ } from 'ct-i18n'
import { registerBlockType } from '@wordpress/blocks'
import { InspectorControls } from '@wordpress/block-editor'
import {
	Panel,
	PanelBody,
	ToggleControl,
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
} from '@wordpress/components'
import Preview from './Preview'
import {
	getAttributesFromOptions,
	getOptionsForBlock,
	OptionsPanel,
} from 'blocksy-options'
import cachedFetch from 'ct-wordpress-helpers/cached-fetch'

export const options = getOptionsForBlock('status_filter')
export const defaultAttributes = getAttributesFromOptions(options)

registerBlockType('blocksy/woocommerce-status-filter', {
	apiVersion: 3,
	title: __('Filter by Status Controls', 'blocksy-companion'),
	description: __('Filter by products status.', 'blocksy-companion'),
	icon: 'filter',
	icon: {
		src: (
			<svg
				xmlns="http://www.w3.org/2000/svg"
				viewBox="0 0 24 24"
				className="wc-block-editor-components-block-icon">
				<path
					fill-rule="evenodd"
					d="M5.3 3h13.4C20 3 21 4 21 5.3v13.4c0 1.3-1 2.3-2.3 2.3H5.3C4 21 3 20 3 18.7V5.3C3 4 4 3 5.3 3Zm13.4 16.5c.4 0 .8-.4.8-.8V5.3c0-.4-.4-.8-.8-.8H5.3c-.4 0-.8.4-.8.8v13.4c0 .4.4.8.8.8h13.4ZM11.924 8.022a.144.144 0 0 0-.053.058l-1.186 2.404a.144.144 0 0 1-.108.078l-2.653.386a.143.143 0 0 0-.08.244l1.92 1.871a.143.143 0 0 1 .041.128l-.453 2.642a.144.144 0 0 0 .208.15l2.373-1.247a.144.144 0 0 1 .133 0l2.374 1.247a.143.143 0 0 0 .207-.15l-.453-2.642a.143.143 0 0 1 .041-.128l1.92-1.871a.144.144 0 0 0-.008-.212.143.143 0 0 0-.071-.032l-2.653-.386a.144.144 0 0 1-.108-.078L12.128 8.08a.143.143 0 0 0-.204-.058Z"
				/>
			</svg>
		),
	},
	category: 'widgets',
	supports: {
		html: false,
		inserter: false,
		lock: false,
	},
	attributes: {
		...defaultAttributes,

		layout: {
			type: 'string',
			default: 'list',
		},

		showCheckboxes: {
			type: 'boolean',
			default: true,
		},

		showCounters: {
			type: 'boolean',
			default: true,
		},

		showResetButton: {
			type: 'boolean',
			default: false,
		},
	},
	edit: ({ attributes, setAttributes }) => {
		const { layout, showResetButton, showCounters, showCheckboxes } =
			attributes

		const [blockData, setBlockData] = useState(null)

		useEffect(() => {
			cachedFetch(
				`${wp.ajax.settings.url}?action=blc_ext_filters_get_block_data`,
				{
					type: 'status',
				}
			)
				.then((response) => response.json())
				.then(({ success, data }) => {
					setBlockData(data)
				})
		}, [])

		return (
			<>
				<Preview attributes={attributes} blockData={blockData} />
				<InspectorControls>
					<Panel header="Filter Settings">
						<PanelBody>
							<ToggleGroupControl
								label={__('Display Type', 'blocksy-companion')}
								value={layout}
								isBlock
								onChange={(newLayout) =>
									setAttributes({ layout: newLayout })
								}>
								<ToggleGroupControlOption
									key="list"
									value="list"
									label={__('List', 'blocksy-companion')}
								/>
								<ToggleGroupControlOption
									key="inline"
									value="inline"
									label={__('Inline', 'blocksy-companion')}
								/>
							</ToggleGroupControl>

							<OptionsPanel
								purpose={'gutenberg'}
								onChange={(optionId, optionValue) => {
									setAttributes({
										[optionId]: optionValue,
									})
								}}
								options={options}
								value={attributes}
								hasRevertButton={false}
							/>
						</PanelBody>

						<PanelBody>
							<ToggleControl
								label={__(
									'Show Checkboxes',
									'blocksy-companion'
								)}
								checked={showCheckboxes}
								onChange={() =>
									setAttributes({
										showCheckboxes: !showCheckboxes,
									})
								}
							/>
						</PanelBody>

						<PanelBody>
							<ToggleControl
								label={__('Show Counters', 'blocksy-companion')}
								checked={showCounters}
								onChange={() =>
									setAttributes({
										showCounters: !showCounters,
									})
								}
							/>
						</PanelBody>

						<PanelBody>
							<ToggleControl
								label={__(
									'Show Reset Button',
									'blocksy-companion'
								)}
								checked={showResetButton}
								onChange={() =>
									setAttributes({
										showResetButton: !showResetButton,
									})
								}
							/>
						</PanelBody>
					</Panel>
				</InspectorControls>
			</>
		)
	},
	save: function () {
		return <div>Blocksy: Price Filter</div>
	},
})

wp.blocks.registerBlockVariation('blocksy/widgets-wrapper', {
	name: 'blocksy-status-filter',
	title: __('Filter by Status', 'blocksy-companion'),
	attributes: {
		heading: __('Filter', 'blocksy-companion'),
		block: 'blocksy/woocommerce-status-filter',
	},
	isActive: (attributes) =>
		attributes.block === 'blocksy/woocommerce-status-filter',
	icon: {
		src: (
			<svg
				xmlns="http://www.w3.org/2000/svg"
				viewBox="0 0 24 24"
				className="wc-block-editor-components-block-icon">
				<path
					fill-rule="evenodd"
					d="M5.3 3h13.4C20 3 21 4 21 5.3v13.4c0 1.3-1 2.3-2.3 2.3H5.3C4 21 3 20 3 18.7V5.3C3 4 4 3 5.3 3Zm13.4 16.5c.4 0 .8-.4.8-.8V5.3c0-.4-.4-.8-.8-.8H5.3c-.4 0-.8.4-.8.8v13.4c0 .4.4.8.8.8h13.4ZM11.924 8.022a.144.144 0 0 0-.053.058l-1.186 2.404a.144.144 0 0 1-.108.078l-2.653.386a.143.143 0 0 0-.08.244l1.92 1.871a.143.143 0 0 1 .041.128l-.453 2.642a.144.144 0 0 0 .208.15l2.373-1.247a.144.144 0 0 1 .133 0l2.374 1.247a.143.143 0 0 0 .207-.15l-.453-2.642a.143.143 0 0 1 .041-.128l1.92-1.871a.144.144 0 0 0-.008-.212.143.143 0 0 0-.071-.032l-2.653-.386a.144.144 0 0 1-.108-.078L12.128 8.08a.143.143 0 0 0-.204-.058Z"
				/>
			</svg>
		),
	},
})
