<template>
    <div class="pagination-container" v-if="total && pagination.length > 1">
        <div>
            <ul class="pagination-list">
                <li>
                    <a class="pagination-link command" :class="currentPage == 1 ? 'disabled' : ''"
                        @click="handlePreviousPage">
                        <font-awesome-icon icon="fa fa-chevron-left" /> &nbsp; {{ $t('general.prev') }}
                    </a>
                </li>

                <li v-for="page in pagination">
                    <a class="pagination-link" :class="page.isSelected ? 'is-current' : ''"
                        @click="handlePageChange(page.value)">{{ page.value }}</a>
                </li>
                <li>
                    <a class="pagination-link command" :class="currentPage == pages ? 'disabled' : ''"
                        @click="handleNextPage">
                        {{ $t('general.next') }} &nbsp; <font-awesome-icon icon="fa fa-chevron-right" />
                    </a>
                </li>
            </ul>
        </div>
        <div class="pagination-info">
            {{ $t('general.results-position', { 'count1': resultsStart, 'count2': resultsEnd, 'total': total }) }}
        </div>
    </div>
</template>

<script>
export default {
    name: 'Pagination',
    props: [
        'total',
        'perPage',
        'current',
        'pageAction'
    ],
    data() {
        return {
            currentPage: this.current
        }
    },
    computed: {
        pages() {
            return this.perPage ? Math.ceil(this.total / this.perPage) : 0;
        },
        pagination() {
            return this.generatePagination(this.pages, this.currentPage);
        },
        resultsStart() {
            return (this.currentPage - 1) * this.perPage + 1
        },
        resultsEnd() {
            return (this.currentPage * this.perPage) > this.total ? this.total : this.currentPage * this.perPage;
        }
    },
    methods: {
        /**
         * Load the previous page of data
         */
        handlePreviousPage() {
            const newPage = this.currentPage > 1 ? this.currentPage - 1 : 1;
            if (newPage != this.currentPage) {
                this.currentPage = newPage
                this.$store.dispatch(this.pageAction, this.currentPage);
            }
        },

        /**
         * Load the next page of data
         */
        handleNextPage() {
            const newPage = this.currentPage < this.pages ? this.currentPage + 1 : this.pages;
            if (newPage != this.currentPage) {
                this.currentPage = newPage
                this.$store.dispatch(this.pageAction, this.currentPage);
            }

        },

        handlePageChange(page) {
            if (parseInt(page)) {
                if (page != this.currentPage) {
                    this.currentPage = page;
                    this.$store.dispatch(this.pageAction, page);
                }
            }
        },

        generatePagination(pages, current) {
            const maxNumberOfElements = 5;
            const firstPage = 1;
            const lastPage = pages;
            const offset = Math.ceil(maxNumberOfElements / 2);
            const offsetLeft = [...Array(offset).keys()].map((value, index) => Math.abs(value - current) <= firstPage || Math.abs(value - current) === current ? null : Math.abs(value - current)).reverse();
            const offsetRight = [...Array(offset).keys()].map((value, index) => (current + value) >= lastPage || (current + value) === current ? null : (current + value));

            let pagination = [
                firstPage,
                this.addSeparator(current, firstPage, offset),
                ...offsetLeft,
                current !== firstPage && current !== lastPage ? current : null,
                ...offsetRight,
                this.addSeparator(current, lastPage, offset),
            ];

            if (firstPage != lastPage) {
                pagination.push(lastPage)
            }

            return pagination.filter(item => item !== null)
                .map((item, index, items) => {
                    return {
                        value: item,
                        isSelected: item === current
                    };
                });
        },

        addSeparator(current, page, offset) {
            const separator = `...`;
            return Math.abs(page - current) > offset ? separator : null;
        }
    }
}
</script>

<style>
.pagination-info,
.pagination-previous,
.pagination-next,
.pagination-ellipsis,
.pagination-link {
    border: none;
    color: #909090;
    border-radius: 40px;
    font-size: 14px;
    font-weight: 500;
    box-shadow: none !important;

    &.is-current {
        background-color: #dbdef9;
        color: #4a4a4a;
    }

    &.disabled {
        color: #ccc;
        cursor: default;
    }
}

.pagination-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

@media only screen and (max-width: 600px) {
    .pagination-container {
        display: block;
        text-align: center;
        padding: 10px 0;

        .pagination-link,
        .pagination-ellipsis {
            display: none;
            border: 1px solid #909090;

            &.command {
                display: block;
            }

            &.disabled {
                color: #ccc;
                cursor: default;
                border-color: #ccc;
            }
        }
    }
}
</style>
