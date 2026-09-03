const THREE = window.THREE;

// TIMELINE LIGHT CONTROLS: these are the main values to edit.
export const WORLD_TIMELINE_LIGHT = {
  topColor: '#5227FF',
  bottomColor: '#df1e26',
  intensity: 0.72,
  rotationSpeed: 0.8,
  glowAmount: 0.0021,
  pillarWidth: 2,
  pillarHeight: 0.2,
  noiseIntensity: 0.18,
  pillarRotation: 90,
};

const vertexShader = `varying vec2 vUv; void main(){vUv=uv;gl_Position=vec4(position,1.0);}`;
const fragmentShader = `
precision mediump float;
uniform float uTime; uniform vec2 uResolution; uniform vec3 uTopColor; uniform vec3 uBottomColor;
uniform float uIntensity; uniform float uGlowAmount; uniform float uPillarWidth; uniform float uPillarHeight;
uniform float uNoiseIntensity; uniform float uPillarRotCos; uniform float uPillarRotSin; varying vec2 vUv;
void main(){
  vec2 uv=(vUv*2.0-1.0)*vec2(uResolution.x/uResolution.y,1.0);
  uv.y+=0.32;
  uv=vec2(uPillarRotCos*uv.x-uPillarRotSin*uv.y,uPillarRotSin*uv.x+uPillarRotCos*uv.y);
  vec3 ro=vec3(0.0,0.0,-10.0); vec3 rd=normalize(vec3(uv,1.0));
  float rc=cos(uTime*0.3),rs=sin(uTime*0.3); vec3 col=vec3(0.0); float t=0.1;
  for(int i=0;i<40;i++){
    vec3 p=ro+rd*t; p.xz=vec2(rc*p.x-rs*p.z,rs*p.x+rc*p.z); vec3 q=p;
    q.y=p.y*uPillarHeight+uTime; float freq=1.0,amp=1.0;
    for(int j=0;j<2;j++){
      float ws=sin(0.4),wc=cos(0.4); q.xz=vec2(wc*q.x-ws*q.z,ws*q.x+wc*q.z);
      q+=cos(q.zxy*freq-uTime*float(j)*2.0)*amp; freq*=2.0; amp*=0.5;
    }
    float d=length(cos(q.xz))-0.2; float bound=length(p.xz)-uPillarWidth;
    float h=max(4.0-abs(d-bound),0.0); d=max(d,bound)+h*h*0.015625; d=abs(d)*0.15+0.01;
    float grad=clamp((15.0-p.y)/30.0,0.0,1.0); col+=mix(uBottomColor,uTopColor,grad)/d;
    t+=d*1.2; if(t>50.0) break;
  }
  col=tanh(col*uGlowAmount/(uPillarWidth/3.0));
  col-=fract(sin(dot(gl_FragCoord.xy,vec2(12.9898,78.233)))*43758.5453)/15.0*uNoiseIntensity;
  float horizontalFade=smoothstep(0.0,0.14,vUv.x)*smoothstep(0.0,0.14,1.0-vUv.x);
  vec3 result=clamp(col*uIntensity,0.0,1.0)*horizontalFade;
  gl_FragColor=vec4(result,1.0);
}`;

const createLightPillar = (container) => {
  const c = WORLD_TIMELINE_LIGHT;
  const scene = new THREE.Scene();
  const camera = new THREE.OrthographicCamera(-1, 1, 1, -1, 0, 1);
  const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: false, powerPreference: 'low-power' });
  const angle = c.pillarRotation * Math.PI / 180;
  const material = new THREE.ShaderMaterial({
    vertexShader, fragmentShader, transparent: true, blending: THREE.AdditiveBlending,
    depthWrite: false, depthTest: false,
    uniforms: {
      uTime:{value:0}, uResolution:{value:new THREE.Vector2(1,1)},
      uTopColor:{value:new THREE.Color(c.topColor)}, uBottomColor:{value:new THREE.Color(c.bottomColor)},
      uIntensity:{value:c.intensity}, uGlowAmount:{value:c.glowAmount}, uPillarWidth:{value:c.pillarWidth},
      uPillarHeight:{value:c.pillarHeight}, uNoiseIntensity:{value:c.noiseIntensity},
      uPillarRotCos:{value:Math.cos(angle)}, uPillarRotSin:{value:Math.sin(angle)},
    },
  });
  const geometry = new THREE.PlaneGeometry(2,2);
  scene.add(new THREE.Mesh(geometry,material)); container.appendChild(renderer.domElement);
  const resize=()=>{const w=container.clientWidth,h=container.clientHeight;renderer.setPixelRatio(Math.min(devicePixelRatio||1,1.25));renderer.setSize(w,h,false);material.uniforms.uResolution.value.set(w,h);};
  resize(); const ro=new ResizeObserver(resize); ro.observe(container);
  const reduced=matchMedia('(prefers-reduced-motion: reduce)').matches; let raf=0,running=true,last=performance.now();
  const draw=(now)=>{if(!running)return;if(!reduced)material.uniforms.uTime.value+=Math.min((now-last)/1000,.05)*c.rotationSpeed;last=now;renderer.render(scene,camera);if(!reduced)raf=requestAnimationFrame(draw);};
  raf=requestAnimationFrame(draw);
  new IntersectionObserver(([entry])=>{if(entry.isIntersecting&&!running){running=true;last=performance.now();raf=requestAnimationFrame(draw);}else if(!entry.isIntersecting&&running){running=false;cancelAnimationFrame(raf);}}).observe(container);
};

export const initWorldTimelineLight = () => {
  document.querySelectorAll('[data-world-timeline-light]').forEach((container) => {
    try { createLightPillar(container); } catch (error) { container.classList.add('is-fallback'); }
  });

  document.querySelectorAll('.world-page__timeline').forEach((timeline) => {
    const scroller = timeline.querySelector('.world-page__timeline-scroll');
    const previous = timeline.querySelector('[data-timeline-previous]');
    const next = timeline.querySelector('[data-timeline-next]');
    if (!scroller || !previous || !next) return;

    const updateControls = () => {
      const maximum = Math.max(0, scroller.scrollWidth - scroller.clientWidth);
      previous.disabled = scroller.scrollLeft <= 2;
      next.disabled = scroller.scrollLeft >= maximum - 2;
    };
    const move = (direction) => {
      scroller.scrollBy({ left: direction * scroller.clientWidth * 0.72, behavior: 'smooth' });
    };

    previous.addEventListener('click', () => move(-1));
    next.addEventListener('click', () => move(1));
    scroller.addEventListener('scroll', updateControls, { passive: true });
    new ResizeObserver(updateControls).observe(scroller);
    updateControls();
  });

  document.querySelectorAll('.world-page__content .wp-block-tab-list').forEach((tabList) => {
    if (tabList.previousElementSibling?.classList.contains('world-page__mobile-tab-select')) return;
    const tabs = [...tabList.querySelectorAll('[role="tab"]')];
    if (!tabs.length) return;

    const selectWrap = document.createElement('div');
    selectWrap.className = 'world-page__mobile-tab-select';
    const select = document.createElement('select');
    select.setAttribute('aria-label', 'Choose a topic');

    tabs.forEach((tab, index) => {
      const option = document.createElement('option');
      option.value = String(index);
      option.textContent = tab.textContent.trim();
      option.selected = tab.getAttribute('aria-selected') === 'true';
      select.appendChild(option);
    });

    const syncSelectedTab = () => {
      const selectedIndex = tabs.findIndex((tab) => tab.getAttribute('aria-selected') === 'true');
      if (selectedIndex >= 0) select.value = String(selectedIndex);
    };

    select.addEventListener('change', () => tabs[Number(select.value)]?.click());
    tabs.forEach((tab) => tab.addEventListener('click', () => window.setTimeout(syncSelectedTab, 0)));
    selectWrap.appendChild(select);
    tabList.before(selectWrap);
  });
};
