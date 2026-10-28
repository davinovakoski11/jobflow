import { createApp } from "vue";
import { Quasar, Notify, Dialog } from "quasar";
import ptBR from "quasar/lang/pt-BR";
import "@quasar/extras/material-icons/material-icons.css";
import "quasar/src/css/index.sass";
import "./style.css";
import App from "./App.vue";

createApp(App)
  .use(Quasar, {
    plugins: { Notify, Dialog },
    lang: ptBR,
    config: {
      brand: { primary: "#514bdf", secondary: "#139c85", negative: "#c5475d" },
    },
  })
  .mount("#app");
