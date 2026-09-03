import { useSiteConfig, useDeviceImages } from "../lib/fastfix";

/**
 * Cartes « Réparations populaires » en haut de la page Prix.
 * Éditables dans wp-admin → FastFix → Contenus des pages → Page Prix,
 * au format : Réparation | Durée | Garantie | Prix
 */

type Row = string[];

interface Props {
  fallback: Row[];
  /** Images livrées avec le site, par libellé de réparation */
  images: Record<string, string>;
}

const GENERIC_IMAGE = "/images/devices/iphone-16.webp";

/** Devine le modèle concerné à partir du libellé (« Écran iPhone 16 » → « iPhone 16 »). */
function guessModel(label: string) {
  return label.replace(/^(Écran|Batterie|Vitre arrière|Caméra|Remplacement)\s+/i, "").trim();
}

export default function PriceCards({ fallback, images }: Props) {
  const config = useSiteConfig();
  const deviceImage = useDeviceImages();

  const remote = config?.content?.["prix.popular.items"] as unknown as Row[] | undefined;
  const rows: Row[] = Array.isArray(remote) && remote.length > 0 ? remote : fallback;

  return (
    <div data-reveal-children className="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
      {rows.map((row) => {
        const [label, time, warranty, price] = row;
        return (
          <div key={label} className="card group overflow-hidden">
            <div className="aspect-square bg-bg-alt p-6 flex items-center justify-center">
              <img
                src={deviceImage(guessModel(label), images[label] ?? GENERIC_IMAGE)}
                alt={label}
                className="h-32 w-auto object-contain transition-transform duration-300 group-hover:scale-105"
                loading="lazy"
              />
            </div>
            <div className="p-5">
              <h3 className="font-display font-bold text-sm mb-2">{label}</h3>
              <div className="flex items-center gap-2 mb-3">
                {time && (
                  <span className="badge badge-purple text-xs">
                    <svg className="h-3 w-3" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {time}
                  </span>
                )}
                {warranty && (
                  <span className="badge badge-green text-xs">
                    <svg className="h-3 w-3" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                    {warranty}
                  </span>
                )}
              </div>
              <div className="flex items-center justify-between">
                <span className="font-display text-xl font-bold text-primary">€ {price}</span>
                <a href="/rdv" className="text-xs font-semibold text-primary hover:underline">
                  Réserver →
                </a>
              </div>
            </div>
          </div>
        );
      })}
    </div>
  );
}
