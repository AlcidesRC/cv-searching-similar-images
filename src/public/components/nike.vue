<template>
  <section id="ecommerce">
    <header>
      <nav>
        <ul>
          <li>Buscar una tienda</li>
          <li>Ayuda</li>
          <li>Únete a nosotros</li>
          <li>Iniciar sesión</li>
        </ul>
      </nav>

      <search>
        <div>
          <strong>Novedades y destacados</strong>
          <strong>Hombre</strong>
          <strong>Mujer</strong>
          <strong>Niño/a</strong>
          <strong>Ofertas</strong>
        </div>

        <aside>
          <span>
            <ul>
              <li :class="{ selected: this.selected === 1 }"><img id="image-1" @click="searchByImage(1)" src="/images/f9645056-9dc0-4b24-9006-57bf348e8f47.webp" /></li>
              <li :class="{ selected: this.selected === 2 }"><img id="image-2" @click="searchByImage(2)" src="/images/fe822acd-2297-42b5-b330-75138a8a6921.webp" /></li>
              <li :class="{ selected: this.selected === 3 }"><img id="image-3" @click="searchByImage(3)" src="/images/fb5ad585-ec9c-4ab6-ab6f-b0b6b50235a4.webp" /></li>
              <li :class="{ selected: this.selected === 4 }"><img id="image-4" @click="searchByImage(4)" src="/images/fefa637e-a7f3-4ca9-ad48-28432820dc92.webp" /></li>
            </ul>

            <i class="fa-solid fa-camera"></i>
          </span>

          <input type="search" placeholder="Buscar" />

          <i class="fa-solid fa-heart"></i>

          <i class="fa-solid fa-bag-shopping"></i>
        </aside>
      </search>

      <menu>
        <i class="fa-solid fa-angle-left"></i>

        <span>
        COMPRAR NOVEDADES
        <a>Comprar</a>
      </span>

        <i class="fa-solid fa-angle-right"></i>
      </menu>
    </header>

    <main>
      <h2>Zapatillas para hombre (<span>{{ totalSneakers }}</span>)</h2>

      <nav>
        <a v-if="selected !== null" class="reset" @click="loadDefault()">
          Reiniciar filtros
          <i class="fa-solid fa-times"></i>
        </a>

        <a>
          Ocultar filtros
          <i class="fa-solid fa-filter"></i>
        </a>

        <a>
          Ordenar por
          <i class="fa-solid fa-sort"></i>
        </a>
      </nav>
    </main>

    <footer>
      <ul>
        <li>Lifestyle</li>
        <li>Jordan</li>
        <li>Running</li>
        <li>Baloncesto</li>
        <li>Fútbol</li>
      </ul>

      <div>
        <figure v-for="item in sneakers">
          <img :src="item.image" />

          <figcaption>
            <h6 >{{ item.name }} <sup v-if="selected">Distancia: {{ item._distance }}</sup></h6>

            <span>{{ item.category }}</span>
            <small>{{ item.models }}</small>
            <strong>{{ item.price }}</strong>
          </figcaption>
        </figure>
      </div>
    </footer>
  </section>
</template>


<script>
module.exports = {
  data() {
    return {
      sneakers: [],
      selected: null
    }
  },
  computed: {
    totalSneakers: function() {
      return this.sneakers.length;
    }
  },
  mounted() {
    this.loadDefault();
  },
  methods: {
    loadDefault() {
      this.selected = null;
      axios.get('/api.json.php').then(response => (this.sneakers = response.data));
    },
    searchByImage(id) {
      this.selected = id;

      let imageSrc = document.getElementById("image-" + id).src;

      axios.post('/api.json.php', {
        src: imageSrc
      }, {
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        }
      }).then(response => (this.sneakers = response.data));
    }
  }
}
</script>


<style>
@import url("https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&display=swap");
@keyframes pulse-animation {
  0% {
    box-shadow: 0 0 0 0px rgba(213, 128, 255, 0.25);
  }
  100% {
    box-shadow: 0 0 0 15px rgba(213, 128, 255, 0);
  }
}
* {
  margin: 0;
  padding: 0;
}
*:focus {
  outline: none;
}

body {
  font-family: "Montserrat", sans-serif;
  font-size: 11px;
  color: #000000;
}

section#ecommerce {
  margin-bottom: 30px;
}
section#ecommerce header {
  border-bottom: 1px solid #DFDFDF;
}
section#ecommerce header > nav {
  background-color: #f7f7f7;
  padding: 10px 20px;
  font-size: 9px;
  text-align: right;
}
section#ecommerce header > nav ul {
  list-style-type: none;
  margin: 0;
  padding: 0;
}
section#ecommerce header > nav ul li {
  display: inline-block;
  font-weight: 400;
}
section#ecommerce header > nav ul li:not(:first-child):before {
  content: "|";
  padding: 0 10px;
}
section#ecommerce header search {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
section#ecommerce header search div strong {
  font-weight: 600;
  padding: 0 10px;
}
section#ecommerce header search aside {
  position: absolute;
  right: 20px;
}
section#ecommerce header search aside span {
  position: relative;
  margin-right: 10px;
}
section#ecommerce header search aside span i {
  color: #d580ff;
  animation: pulse-animation 2s infinite;
  border-radius: 50%;
  box-shadow: 0 0 1px 1px #3366991a;
}
section#ecommerce header search aside span ul {
  position: absolute;
  top: 100%;
  left: 50%;
  transform: translateX(-50%) translateX(6px) translateY(8px);
  list-style-type: none;
  margin: 0;
  padding: 0;
  border: 2px solid #d580ff;
  background-color: #FFFFFF;
  z-index: 10;
  display: flex;
  flex-direction: row;
  justify-content: center;
  align-items: center;
}
section#ecommerce header search aside span ul:before {
  content: "";
  position: absolute;
  transform: translate(-50%, -100%);
  top: 0;
  left: 50%;
  width: 0;
  height: 0;
  border-style: solid;
  border-width: 0 8px 8px 8px;
  border-color: transparent transparent #d580ff;
}
section#ecommerce header search aside span ul li {
  cursor: pointer;
}
section#ecommerce header search aside span ul li img {
  display: block;
  margin: 0;
  padding: 0;
  width: 70px;
  height: 70px;
  object-fit: cover;
  opacity: 25%;
  filter: grayscale(100%);
}
section#ecommerce header search aside span ul li:hover img, section#ecommerce header search aside span ul li.selected img {
  opacity: 100%;
  filter: grayscale(0%);
}
section#ecommerce header search aside span ul li.selected {
  cursor: crosshair;
}
section#ecommerce header search aside input {
  width: 150px;
  border: 0px none;
  border-radius: 20px;
  background-color: #f7f7f7;
  padding: 5px 20px;
}
section#ecommerce header search aside input::placeholder {
  font-weight: 600;
  color: #999999;
}
section#ecommerce header search aside i {
  margin-left: 15px;
}
section#ecommerce header menu {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 15px 20px;
  background-color: #f7f7f7;
}
section#ecommerce header menu i {
  color: #999999;
  font-size: 16px;
}
section#ecommerce header menu span {
  font-size: 14px;
  font-weight: 300;
  padding: 0 40px;
}
section#ecommerce header menu span a {
  margin-left: 20px;
  color: #000000;
  font-weight: 600;
  font-size: 9px;
}
section#ecommerce main {
  display: flex;
  justify-content: space-between;
  padding: 20px;
}
section#ecommerce main h2 {
  font-weight: 600;
  font-size: 14px;
}
section#ecommerce main h2 span {
  color: #d580ff;
  font-weight: 600;
}
section#ecommerce main nav a {
  color: #000000;
  text-decoration: none;
  cursor: not-allowed;
}
section#ecommerce main nav a.reset {
  color: #d580ff;
  cursor: pointer;
  font-weight: 600;
}
section#ecommerce main nav a:not(:first-child) {
  margin-left: 20px;
}
section#ecommerce footer {
  display: flex;
}
section#ecommerce footer ul {
  list-style-type: none;
  margin: 0;
  padding: 0 20px;
  width: 200px;
}
section#ecommerce footer ul li {
  padding: 10px 0;
}
section#ecommerce footer div {
  display: flex;
  flex-wrap: wrap;
  gap: 40px;
  padding: 0 30px;
}
section#ecommerce footer div figure {
  width: calc(20% - 33px);
}
section#ecommerce footer div figure img {
  width: 100%;
  height: 300px;
  object-fit: cover;
}
section#ecommerce footer div figure figcaption {
  padding: 10px 0 0 0;
}
section#ecommerce footer div figure figcaption h6,
section#ecommerce footer div figure figcaption span,
section#ecommerce footer div figure figcaption small {
  display: block;
}
section#ecommerce footer div figure figcaption h6 {
  font-weight: 600;
  font-size: 12px;
}
section#ecommerce footer div figure figcaption h6 sup {
  color: #d580ff;
}
section#ecommerce footer div figure figcaption small,
section#ecommerce footer div figure figcaption span {
  margin: 5px 0;
  color: #666666;
}
section#ecommerce footer div figure figcaption strong {
  font-size: 14px;
}

@media (min-width: 1281px) {
  section#ecommerce header search {
    justify-content: unset;
  }
  section#ecommerce footer div figure {
    width: calc(20% - 33px);
  }
  section#ecommerce footer div figure img {
    height: 230px;
  }
}
@media (min-width: 1025px) and (max-width: 1280px) {
  section#ecommerce header search {
    justify-content: unset;
  }
  section#ecommerce footer div figure {
    width: calc(25% - 30px);
  }
  section#ecommerce footer div figure img {
    height: 200px;
  }
}
@media (min-width: 768px) and (max-width: 1024px) {
  section#ecommerce header search {
    justify-content: unset;
  }
  section#ecommerce footer div figure {
    width: calc(33% - 25px);
  }
  section#ecommerce footer div figure img {
    height: 200px;
  }
}
@media (min-width: 481px) and (max-width: 767px) {
  section#ecommerce header search {
    justify-content: unset;
  }
  section#ecommerce footer div figure {
    width: calc(33% - 25px);
  }
  section#ecommerce footer div figure img {
    height: 120px;
  }
}
</style>
