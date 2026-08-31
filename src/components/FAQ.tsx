import { useState } from "react";

const faqs = [
  {
    q: "Combien de temps dure une réparation ?",
    a: "La plupart des réparations de smartphones (écran, batterie) sont terminées en 60 minutes maximum. Pour les MacBooks et tablettes, le délai dépend de la réparation — chaque fiche indique le temps estimé.",
  },
  {
    q: "Utilisez-vous des pièces d'origine ?",
    a: "Oui, nous utilisons des pièces d'origine ou certifiées constructeur chaque fois qu'elles sont disponibles. Pour chaque réparation, nous vous précisons la qualité de la pièce avant de commencer.",
  },
  {
    q: "Quelle garantie offrez-vous ?",
    a: "Toutes nos réparations sont couvertes par une garantie allant jusqu'à 1 an. En cas de problème lié à notre intervention, nous reprenons votre appareil sans frais supplémentaires.",
  },
  {
    q: "Faut-il prendre rendez-vous ?",
    a: "Non, vous pouvez passer sans rendez-vous pendant nos heures d'ouverture. Le rendez-vous en ligne permet simplement de réduire votre attente — vous êtes prioritaire à votre arrivée.",
  },
  {
    q: "Puis-je envoyer mon appareil par la poste ?",
    a: "Absolument. Sélectionnez votre appareil et votre réparation, nous vous envoyons une étiquette prépayée par e-mail. Vous postez quand ça vous arrange et recevez votre appareil réparé sous 3 à 5 jours ouvrés.",
  },
  {
    q: "Combien ça coûte ?",
    a: "Les prix dépendent du modèle et de la réparation. Consultez notre grille tarifaire pour un devis instantané. Pas de surprise : le prix affiché est le prix final.",
  },
];

export default function FAQ() {
  const [openIdx, setOpenIdx] = useState<number | null>(null);

  return (
    <div className="space-y-3">
      {faqs.map((faq, i) => {
        const isOpen = openIdx === i;
        return (
          <div key={i} className="rounded-xl border border-border bg-white overflow-hidden transition-colors hover:border-border-dark">
            <button
              onClick={() => setOpenIdx(isOpen ? null : i)}
              className="flex w-full items-center justify-between gap-4 px-6 py-5 text-left"
              aria-expanded={isOpen}
            >
              <span className="font-display font-semibold text-text text-sm lg:text-base">
                {faq.q}
              </span>
              <div className={`flex h-6 w-6 items-center justify-center rounded-full border flex-shrink-0 transition-all duration-300 ${isOpen ? "bg-primary border-primary rotate-45" : "border-border"}`}>
                <svg className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke={isOpen ? "white" : "currentColor"}>
                  <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
              </div>
            </button>
            <div
              className="overflow-hidden transition-all duration-300"
              style={{ maxHeight: isOpen ? "200px" : "0px", opacity: isOpen ? 1 : 0 }}
            >
              <p className="px-6 pb-5 text-sm leading-relaxed text-text-muted">
                {faq.a}
              </p>
            </div>
          </div>
        );
      })}
    </div>
  );
}
