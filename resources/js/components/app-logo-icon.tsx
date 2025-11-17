import { ImgHTMLAttributes } from 'react';
import logo from '@/assets/logo_leonor_cerna 2.png';

export default function AppLogoIcon(props: ImgHTMLAttributes<HTMLImageElement>) {
    return (
        <img
            src={logo}
            alt="Logo I.E. Leonor Cerna de Valdiviezo"
            {...props}
            className={`${props.className || ''}`}
            onError={(e) => {
                console.error('Error cargando logo:', logo);
                console.error('Evento de error:', e);
            }}
            onLoad={() => {
                console.log('Logo cargado correctamente:', logo);
            }}
        />
    );
}
