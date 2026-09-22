import React from 'react';
import { createRoot } from 'react-dom/client';
import Redaccion from './sala/Redaccion.jsx';
import Aire from './sala/Aire.jsx';

const Screen = window.location.pathname.startsWith('/aire') ? Aire : Redaccion;
createRoot(document.getElementById('app')).render(<Screen />);
