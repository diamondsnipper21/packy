<template>
    <div class="card-element-wrapper">
        <form id="stripe-element-form">
            <div id="stripe-element-mount-point"></div>
            <slot name="stripe-element-errors">
                <div id="stripe-element-errors" role="alert"></div>
            </slot>
            <button ref="submitButtonRef" type="submit" class="hide"></button>
        </form>
    </div>
</template>

<script>
import {loadStripe} from '@stripe/stripe-js';

const ELEMENT_TYPE = 'card';
const DEFAULT_ELEMENT_STYLE = {
    base: {
        color: '#32325d',
        fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
        fontSmoothing: 'antialiased',
        fontSize: '16px',
        '::placeholder': {
            color: '#aab7c4',
        },
    },
    invalid: {
        color: '#fa755a',
        iconColor: '#fa755a',
    },
};
export default {
    props: {
        pk: {
            type: String,
            required: true,
        },
        testMode: {
            type: Boolean,
            default: false,
        },
        stripeAccount: {
            type: String,
            default: undefined,
        },
        apiVersion: {
            type: String,
            default: undefined,
        },
        locale: {
            type: String,
            default: 'auto',
        },
        elementsOptions: {
            type: Object,
            default: () => ({}),
        },
        tokenData: {
            type: Object,
            default: () => ({}),
        },
        disableAdvancedFraudDetection: {
            type: Boolean,
        },
        // element specific options
        classes: {
            type: Object,
            default: () => ({}),
        },
        elementStyle: {
            type: Object,
            default: () => (DEFAULT_ELEMENT_STYLE),
        },
        value: {
            type: String,
            default: undefined,
        },
        hidePostalCode: {
            type: Boolean,
            default: true
        },
        iconStyle: {
            type: String,
            default: 'default',
            validator: value => ['solid', 'default'].includes(value),
        },
        hideIcon: Boolean,
        disabled: Boolean
    },
    data() {
        return {
            loading: false,
            stripe: null,
            elements: null,
            element: null,
            card: null,
        };
    },
    computed: {
        form() {
            return document.getElementById('stripe-element-form');
        },
    },
    async mounted() {
        if (this.disableAdvancedFraudDetection) {
            loadStripe.setLoadParameters({advancedFraudSignals: false});
        }

        const stripeOptions = {
            stripeAccount: this.stripeAccount,
            apiVersion: this.apiVersion,
            locale: this.locale,
        };
        const createOptions = {
            classes: this.classes,
            style: this.elementStyle,
            value: this.value,
            hidePostalCode: this.hidePostalCode,
            iconStyle: this.iconStyle,
            hideIcon: this.hideIcon,
            disabled: this.disabled,
        };

        this.stripe = await loadStripe(this.pk, stripeOptions);
        this.elements = this.stripe.elements(this.elementsOptions);
        this.element = this.elements.create(ELEMENT_TYPE, createOptions);
        this.element.mount('#stripe-element-mount-point');

        this.element.on('change', async (event) => {
            let displayError = document.getElementById('stripe-element-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }

            if (event.complete) {
                try {
                    this.$emit('loading', true);
                    // event.preventDefault();

                    const data = {
                        ...this.element,
                    };
                    if (this.amount) data.amount = this.amount;

                    const result = await this.stripe.createPaymentMethod({
                        type: 'card',
                        card: data,
                    })
                    let token = result.paymentMethod;

                    /*
                    const { token, error } = await this.stripe.createToken(data, this.tokenData);
                    if (error) {
                      const errorElement = document.getElementById('stripe-element-errors');
                      errorElement.textContent = error.message;
                      this.$emit('error', error);
                      return;
                    }
                    */

                    this.$emit('token', token);
                    this.$emit('cardElement', data);
                    this.$emit('stripeInstance', this.stripe);
                } catch (error) {
                    console.error(error);
                    this.$emit('error', error);
                } finally {
                    this.$emit('loading', false);
                }
            }

            this.onChange(event);
        });

        this.element.on('blur', this.onBlur);
        this.element.on('click', this.onClick);
        this.element.on('escape', this.onEscape);
        this.element.on('focus', this.onFocus);
        this.element.on('ready', this.onReady);
    },
    methods: {
        /**
         * Triggers the submission of the form
         * @return {void}
         */
        submit() {
            this.$refs.submitButtonRef.click();
        },
        /**
         * Clears the element
         * @return {void}
         */
        clear() {
            this.element.clear();
        },
        /**
         * Destroys the element
         * @return {void}
         */
        destroy() {
            this.element.destroy();
        },
        /**
         * Focuses on the element
         * @return {void}
         */
        focus() {
            console.warn('This method will currently not work on iOS 13+ due to a system limitation.');
            this.element.focus();
        },
        /**
         * Unmounts the element
         * @return {void}
         */
        unmount() {
            this.element.unmount();
        },
        /**
         * Updates the element
         * @param {string} opts.classes.base The base class applied to the container. Defaults to StripeElement.
         * @param {string} opts.classes.complete The class name to apply when the Element is complete. Defaults to StripeElement--complete.
         * @param {string} opts.classes.empty The class name to apply when the Element is empty. Defaults to StripeElement--empty.
         * @param {string} opts.classes.focus The class name to apply when the Element is focused. Defaults to StripeElement--focus.
         * @param {string} opts.classes.invalid The class name to apply when the Element is invalid. Defaults to StripeElement--invalid.
         * @param {string} opts.classes.webkitAutoFill The class name to apply when the Element has its value autofilled by the browser (only on Chrome and Safari). Defaults to StripeElement--webkit-autofill.
         * @param {Object} opts.style Customize the appearance of this element using CSS properties passed in a Style object.
         * @param {string} opts.value A pre-filled set of values to include in the input (e.g., {postalCode: '94110'}). Note that sensitive card information (card number, CVC, and expiration date) cannot be pre-filled
         * @param {boolean} opts.hidePostalCode Hide the postal code field. Default is false. If you are already collecting a full billing address or postal code elsewhere, set this to true.
         * @param {string} opts.iconStyle Appearance of the icon in the Element. Either solid or default.
         * @param {boolean} opts.hideIcon Hides the icon in the Element. Default is false.
         * @param {boolean} opts.disabled Applies a disabled state to the Element such that user input is not accepted. Default is false.
         */
        update(opts) {
            this.element.update(opts);
        },
        // events
        onChange(e) {
            this.$emit('element-change', e);
        },
        onReady(e) {
            this.$emit('element-ready', e);
        },
        onFocus(e) {
            this.$emit('element-focus', e);
        },
        onBlur(e) {
            this.$emit('element-blur', e);
        },
        onEscape(e) {
            this.$emit('element-escape', e);
        },
        onClick(e) {
            this.$emit('element-click', e);
        },
    },
};
</script>

<style scoped>
.card-element-wrapper {
    width: 100%;
}

/**
   * The CSS shown here will not be introduced in the Quickstart guide, but shows
   * how you can use CSS to style your Element's container.
   */
.StripeElement {
    box-sizing: border-box;

    height: 40px;

    padding: 10px 12px;

    border: 1px solid transparent;
    border-radius: 4px;
    background-color: white;
    border-color: #dbdbdb;

    box-shadow: inset 0 1px 2px rgba(10, 10, 10, 0.1);
    -webkit-transition: box-shadow 150ms ease;
    transition: box-shadow 150ms ease;
}

.StripeElement--focus {
    box-shadow: 0 1px 3px 0 #cfd7df;
}

.StripeElement--invalid {
    border-color: #fa755a;
}

.StripeElement--webkit-autofill {
    background-color: #fefde5 !important;
}

.hide {
    display: none;
}
</style>