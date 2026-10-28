    </main>

    <!-- BARRA FLUTUANTE DE EMERGÊNCIA RÁPIDA NO MOBILE (FIXA NO RODAPÉ EM TELAS PEQUENAS) -->
    <div class="md:hidden fixed bottom-3 left-3 right-3 z-30 flex items-center gap-2 bg-musgo-950/95 text-white p-2 rounded-2xl shadow-2xl backdrop-blur-md border border-musgo-800/80">
        <a href="tel:192" class="flex-1 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white py-2.5 px-3 rounded-xl font-black text-center text-xs flex items-center justify-center gap-2 shadow-md shadow-emerald-900/30 transition-all">
            <i class="fas fa-phone-volume text-sm animate-bounce text-yellow-300"></i>
            <span>LIGAR 192 (SAMU)</span>
        </a>
        <a href="tel:193" class="bg-musgo-800 hover:bg-musgo-700 active:scale-95 text-white py-2.5 px-3 rounded-xl font-bold text-center text-xs flex items-center justify-center gap-1.5 transition-all">
            <i class="fas fa-fire-extinguisher text-amber-300"></i>
            <span>193</span>
        </a>
    </div>

    <!-- RODAPÉ COMPLETO (VERDE MUSGO PROFUNDO) -->
    <footer class="bg-musgo-950 text-musgo-200 border-t border-musgo-900 mt-16 pt-12 pb-24 md:pb-12 text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                
                <!-- Coluna 1: Sobre o Projeto -->
                <div class="md:col-span-2 space-y-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg bg-musgo-700 flex items-center justify-center text-white font-bold shadow-md">
                            <i class="fas fa-flask text-musgo-100"></i>
                        </div>
                        <span class="text-xl font-black text-white tracking-tight">SOS <span class="text-musgo-400">Química</span></span>
                    </div>
                    <p class="text-musgo-300 text-xs sm:text-sm leading-relaxed max-w-lg">
                        Sistema rápido de suporte e resposta a acidentes em laboratórios químicos de ensino e pesquisa.
                        Desenvolvido para oferecer procedimentos imediatos, sem necessidade de login, priorizando a segurança de alunos e docentes.
                    </p>
                    <div class="flex items-center gap-3 pt-1">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-musgo-900 text-musgo-300 border border-musgo-800">
                            <i class="fas fa-shield-halved text-emerald-400"></i> PDO Seguro
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-musgo-900 text-musgo-300 border border-musgo-800">
                            <i class="fas fa-key text-yellow-300"></i> Hash Bcrypt
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-musgo-900 text-musgo-300 border border-musgo-800">
                            <i class="fas fa-clock-rotate-left text-amber-300"></i> Auditoria Ativa
                        </span>
                    </div>
                </div>

                <!-- Coluna 2: Telefones de Socorro -->
                <div>
                    <h4 class="text-white font-bold text-sm uppercase tracking-wider mb-3 flex items-center gap-2">
                        <i class="fas fa-phone-flip text-emerald-400"></i> Telefones Críticos
                    </h4>
                    <ul class="space-y-2 text-xs">
                        <li>
                            <a href="tel:192" class="hover:text-white transition-colors flex items-center justify-between py-1 border-b border-musgo-900 text-musgo-200">
                                <span>SAMU (Urgência Médica)</span>
                                <span class="font-bold text-white bg-emerald-900/60 px-2 py-0.5 rounded border border-emerald-700/50">192</span>
                            </a>
                        </li>
                        <li>
                            <a href="tel:193" class="hover:text-white transition-colors flex items-center justify-between py-1 border-b border-musgo-900 text-musgo-200">
                                <span>Corpo de Bombeiros</span>
                                <span class="font-bold text-white bg-amber-900/60 px-2 py-0.5 rounded border border-amber-700/50">193</span>
                            </a>
                        </li>
                        <li>
                            <a href="tel:08007226001" class="hover:text-white transition-colors flex items-center justify-between py-1 border-b border-musgo-900 text-musgo-200">
                                <span>CIT (Disque-Intoxicação)</span>
                                <span class="font-bold text-white bg-musgo-800 px-2 py-0.5 rounded border border-musgo-700">0800 722 6001</span>
                            </a>
                        </li>
                        <li>
                            <a href="tel:199" class="hover:text-white transition-colors flex items-center justify-between py-1 text-musgo-200">
                                <span>Defesa Civil</span>
                                <span class="font-bold text-white bg-musgo-800 px-2 py-0.5 rounded border border-musgo-700">199</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Coluna 3: Acesso Rápido -->
                <div>
                    <h4 class="text-white font-bold text-sm uppercase tracking-wider mb-3 flex items-center gap-2">
                        <i class="fas fa-compass text-musgo-400"></i> Navegação
                    </h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="<?= url('index.php') ?>" class="hover:text-white transition-colors">🚨 SOS de Emergência</a></li>
                        <li><a href="<?= url('pesquisa.php') ?>" class="hover:text-white transition-colors">🔬 Pesquisa de Reagentes & FISPQ</a></li>
                        <li><a href="<?= url('historico.php') ?>" class="hover:text-white transition-colors">📋 Histórico de Logs & Auditoria</a></li>
                        <li><a href="<?= url('admin/index.php') ?>" class="hover:text-white transition-colors">⚙️ Gestão de Procedimentos</a></li>
                        <li><a href="https://github.com/yasminos33/SOS-quimica.git" target="_blank" rel="noopener" class="hover:text-white transition-colors flex items-center gap-1.5"><i class="fab fa-github"></i> Repositório GitHub</a></li>
                    </ul>
                </div>

            </div>

            <!-- Faixa Inferior -->
            <div class="border-t border-musgo-900 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-musgo-400">
                <p>© <?= date('Y') ?> SOS Química. Desenvolvido para proteção e resposta a acidentes em laboratórios químicos.</p>
                <p class="flex items-center gap-1">
                    <span>Priorize sempre o uso de EPIs antes do socorro.</span>
                </p>
            </div>
        </div>
    </footer>

    <!-- Scripts da Aplicação -->
    <script src="<?= url('assets/js/app.js') ?>"></script>
</body>
</html>
