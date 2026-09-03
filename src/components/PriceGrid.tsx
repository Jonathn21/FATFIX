import { useSiteConfig, useDeviceImages } from "../lib/fastfix";

/**
 * Grille tarifaire de la page Prix : une section par catégorie, une carte par
 * modèle avec ses prix (écran, batterie, vitre arrière, caméra).
 *
 * Les catégories, modèles et prix se règlent dans
 * wp-admin → FastFix → Contenus des pages → Page Prix.
 * Les photos viennent du catalogue Appareils (correspondance par nom) et
 * retombent sur les images livrées avec le site si besoin.
 */

type Row = string[]; // [modèle, écran, batterie, vitre arrière, caméra]

interface Category {
  name: string;
  logo?: string | null;
  rows: Row[];
}

interface Props {
  fallback: Category[];
  /** Images livrées avec le site, par nom de modèle */
  images: Record<string, string>;
}

const PRICE_LABELS = [
  { key: 1, label: "Écran", icon: <rect x="5" y="2" width="14" height="20" rx="2" /> },
  { key: 2, label: "Batterie", icon: <><rect x="6" y="7" width="12" height="10" rx="1" /><line x1="10" y1="7" x2="10" y2="4" /><line x1="14" y1="7" x2="14" y2="4" /></> },
  { key: 3, label: "Vitre arr.", icon: <rect x="4" y="2" width="16" height="20" rx="2" /> },
  { key: 4, label: "Caméra", icon: <circle cx="12" cy="12" r="3" /> },
];

const GENERIC_IMAGE = "/images/devices/iphone-16.webp";

export default function PriceGrid({ fallback, images }: Props) {
  const config = useSiteConfig();
  const deviceImage = useDeviceImages();
  const content = config?.content ?? {};

  // Chaque catégorie est un couple « nom + liste de modèles » dans WordPress.
  const categories: Category[] = [1, 2, 3, 4]
    .map((i, index) => {
      const rows = content[`prix.grid.cat${i}_rows`] as unknown as Row[] | undefined;
      const name = content[`prix.grid.cat${i}_name`] as string | undefined;
      if (Array.isArray(rows) && rows.length > 0) {
        return { name: name ?? fallback[index]?.name ?? "", logo: fallback[index]?.logo, rows };
      }
      return fallback[index];
    })
    .filter((cat): cat is Category => Boolean(cat && cat.rows?.length));

  return (
    <>
      {categories.map((cat) => (
        <div key={cat.name} className="mb-14" data-reveal>
          <div className="flex items-center gap-3 mb-6">
            {cat.logo && <img src={cat.logo} alt="" className="h-6 opacity-50" style={{ filter: "brightness(0)" }} />}
            <h3 className="font-display text-xl font-bold">{cat.name}</h3>
          </div>

          <div className="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
            {cat.rows.map((row) => {
              const model = row[0];
              const prices = PRICE_LABELS.filter((p) => row[p.key] && row[p.key].trim() !== "");

              return (
                <div key={model} className="card overflow-hidden flex flex-col">
                  <div className="bg-white p-4 flex items-center justify-center h-36">
                    <img
                      src={deviceImage(model, images[model] ?? GENERIC_IMAGE)}
                      alt={model}
                      className="h-28 w-auto object-contain"
                      loading="lazy"
                    />
                  </div>
                  <div className="p-4 bg-bg-alt border-t border-border flex-1">
                    <h4 className="font-display font-bold text-sm mb-3">{model}</h4>
                    <div className="space-y-2 text-xs">
                      {prices.map((p) => (
                        <div key={p.label} className="flex items-center justify-between">
                          <span className="text-text-muted flex items-center gap-1.5">
                            <svg className="h-3 w-3 text-primary" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor">
                              {p.icon}
                            </svg>
                            {p.label}
                          </span>
                          <span className="font-display font-bold text-primary">€ {row[p.key]}</span>
                        </div>
                      ))}

                      {prices.length === 0 && (
                        <div className="text-center py-1">
                          <a href="/contact" className="text-primary font-semibold hover:underline">
                            Devis sur demande
                          </a>
                        </div>
                      )}
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      ))}
    </>
  );
}
