function getTS() {
  const el = document.getElementById("nr");
  return el && el.tomselect ? el.tomselect : null;
}

const el = document.getElementById("nr");
const isLoggedIn = JSON.parse(
  "{if $f3->get("SESSION.CURRENT_USER")}true{else}false{/if}"
);

// const MIN_LEN = parseInt(
//   el.dataset.minlen || "{GK_SITE_TRACKING_CODE_MIN_LENGTH}",
//   10
// );
// const MAX_LEN = parseInt(
//   el.dataset.maxlen || "{GK_SITE_TRACKING_CODE_MAX_LENGTH}",
//   10
// );

// helper: trim & normalize
const norm = (s) => (s || "").toString().trim().toUpperCase();

const plugins = {
  remove_button: {
    title: "{t}Remove selected item{/t}",
  },
  clear_button: {
    title: "{t}Remove all selected items{/t}",
  },
};

const options = {
  maxItems: isLoggedIn ? parseInt("{GK_CHECK_WAYPOINT_NAME_COUNT}", 10) : 1,
  persist: false,
  addPrecedence: true,
  hidePlaceholder: true,
  closeAfterSelect: true,
  createOnBlur: true,
  duplicates: false,
  preload: isLoggedIn,
  valueField: "tracking_code",
  labelField: "label",
  // searchField: ["name", "tracking_code", "gkid"],
  searchField: [],
  plugins: plugins,

  // TODO this is bugged, it prevents adding new items
  // // min/max
  // createFilter: function (input) {
  //   const v = norm(input);
  //   if (v.length < MIN_LEN || v.length > MAX_LEN) return false;
  // },

  create(input) {
    const v = norm(input);
    return {
      tracking_code: v,
      label: v,
      name: "",
      gkid: "",
    };
  },
  render: {
    option_create(data, escape) {
      return (
        "<div class=\"create\">" +
        "{t}Add tracking code{/t}: <strong>" +
        escape(data.input).toUpperCase() +
        "</strong>" +
        "</div>"
      );
    },
  },
};

// add dropdown header plugin only if logged in
if (isLoggedIn) {
  options.plugins.dropdown_header = {
    title: "{t}Your inventory{/t}",
  };

  options.load = function (query, callback) {
    fetch("{"geokrety_move_select_from_inventory"|alias}", {
      headers: {
        Accept: "application/json",
      },
    })
      .then((r) => r.json())
      .then((data) => callback(data))
      .catch(() => callback());
  };
}

if (!isLoggedIn) {
  options.render = options.render || {};
  options.render.no_results = function () {
    return "";
  };
}

const ts = new TomSelect($("#nr"), options);

// TODO temporarily disabled
//   // Guardrail for paste/enter
//   ts.on("item_add", (value) => {
//     const v = norm(value);
//     console.log(value);
//     if (v.length < MIN_LEN || v.length > MAX_LEN) {
//       ts.removeItem(value);
//       ts.removeOption(value);
//     }
//   });
