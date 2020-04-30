<template></template>

<script>
export default {
    props: {
        message: String,
        type: String
    },
    mounted() {
        this.$toasted.show(this.message, {
            theme: "bubble",
            duration: 3000,
            position: "bottom-center",
        });
    },
    render() {
        return "";
    }
};
</script>

<style>
</style>
