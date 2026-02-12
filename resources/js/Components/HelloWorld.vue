<template>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Hello Vue Component</h3>
        </div>
        <div class="card-body">
            <p>{{ message }}</p>
            <button class="btn btn-primary" @click="changeMessage">Click Me</button>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            message: 'Welcome to your first Vue component in Laravel + AdminLTE!'
        }
    },
    methods: {
        changeMessage() {
            this.message = 'You clicked the button! 🎉';
        }
    }
}
</script>

<style scoped>
.card {
    margin-top: 20px;
}
</style>