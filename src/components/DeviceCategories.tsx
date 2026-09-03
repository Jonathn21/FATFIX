import { useState, useEffect } from "react";
import { FASTFIX_API_URL } from "../lib/fastfix";

interface Category {
  name: string;
  image: string;
  link: string;
}

// Grille livrée avec le site, affichée tant que WordPress n'a pas répondu
// et si l'API est injoignable.
const fallbackCategories: Category[] = [
  { name: "iPhone", image: "/images/devices/iphone-16.webp", link: "/reparations" },
  { name: "Samsung", image: "/images/devices/galaxy-s26-ultra.webp", link: "/reparations" },
  { name: "iPad", image: "/images/devices/ipad-pro.webp", link: "/reparations" },
  { name: "MacBook", image: "/images/devices/macbook-air.webp", link: "/reparations" },
  { name: "Apple Watch", image: "/images/devices/apple-watch.webp", link: "/reparations" },
  { name: "iMac", image: "/images/devices/imac.webp", link: "/reparations" },
  { name: "Samsung Galaxy Tab", image: "/images/devices/galaxy-tab.webp", link: "/reparations" },
  { name: "PlayStation", image: "/images/devices/ps5.webp", link: "/reparations" },
  { name: "Google Pixel", image: "/images/devices/pixel-10-pro.webp", link: "/reparations" },
  { name: "OnePlus", image: "/images/devices/oneplus-13.webp", link: "/reparations" },
  { name: "Xbox", image: "/images/devices/xbox-series-x.webp", link: "/reparations" },
  { name: "AirPods", image: "/images/devices/airpods-max.webp", link: "/reparations" },
];

export default function DeviceCategories() {
  const [categories, setCategories] = useState<Category[]>(fallbackCategories);

  // Catégories éditables dans wp-admin → FastFix → Catégories.
  useEffect(() => {
    fetch(`${FASTFIX_API_URL}/categories`)
      .then((res) => (res.ok ? res.json() : Promise.reject(res)))
      .then((list: Category[]) => {
        if (!Array.isArray(list) || list.length === 0) return;
        // Une catégorie sans photo uploadée retombe sur l'image d'origine
        // du site quand le nom correspond, sinon sur une image générique.
        setCategories(
          list.map((c) => ({
            ...c,
            image:
              c.image ||
              fallbackCategories.find((f) => f.name === c.name)?.image ||
              "/images/devices/iphone-16.webp",
          }))
        );
      })
      .catch(() => {});
  }, []);

  return (
    <div data-reveal-children className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
      {categories.map((cat) => (
        <a
          key={cat.name}
          href={cat.link || "/reparations"}
          className="group rounded-2xl bg-white border border-border p-6 flex flex-col items-center text-center transition-all hover:shadow-lg hover:-translate-y-1"
        >
          <div className="h-24 sm:h-28 flex items-center justify-center mb-4">
            <img
              src={cat.image}
              alt={cat.name}
              className="max-h-full max-w-full object-contain transition-transform duration-300 group-hover:scale-110"
              loading="lazy"
            />
          </div>
          <span className="font-display font-semibold text-sm text-text">{cat.name}</span>
        </a>
      ))}
    </div>
  );
}
