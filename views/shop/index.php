<?php

$this->title = 'Товары';

?>

<div id="app">
<!--{{ products | json }}-->

    <div v-for="product in products">
        {{ product.id }}
        {{ product.name }}
        {{ product.description }}
        {{ product.price }}
        {{ product.quantity }}
        {{ product.image }}
    </div>
<!--    <div v-component="products">-->
<!---->
<!--    </div>-->
<!---->
<!--    <div v-component="cart">-->
<!--        -->
<!--    </div>-->
</div>


<button class="btn btn-default btn-lg"
        @click="showRight = !showRight">
    Click to toggle alert on right
</button>

<button class="btn btn-default btn-lg"
        @click="showTop = !showTop">
    Click to toggle alert on top
</button>
<hr>
<alert type="success" >
    <strong>Well Done!</strong>
    You successfully read this important alert message.
</alert>

<alert type="info" >
    <strong>Heads up!</strong> This alert needs your attention, but it's not super important.
</alert>