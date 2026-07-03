import streamlit as st
import pandas as pd
import base64
import pickle
import os
from pgmpy.inference import VariableElimination

# 1. Configuração da Página
st.set_page_config(page_title="Simulador ENEM - IA", layout="wide")

# Caminho dos dados locais fornecido por você
PASTA_DADOS = r"C:\Users\Lucas\Documents\UFMG\Iniciação Científica\Aprendizagem\ENEM2024DADOS\DADOS"

# ==============================================================================
# DICIONÁRIO DE HABILIDADES DE MATEMÁTICA
# ==============================================================================
DESCRICOES_HABILIDADES = {
    "H_1": "H1 - Reconhecer, no contexto social, diferentes significados e representações dos números e operações - naturais, inteiros, racionais ou reais.",
    "H_2": "H2 - Identificar padrões numéricos ou princípios de contagem.",
    "H_3": "H3 - Resolver situação-problema envolvendo conhecimentos numéricos.",
    "H_4": "H4 - Avaliar a razoabilidade de um resultado numérico na construção de argumentos sobre afirmações quantitativas.",
    "H_5": "H5 - Avaliar propostas de intervenção na realidade utilizando conhecimentos numéricos.",
    "H_6": "H6 - Interpretar a localização e a movimentação de pessoas/objetos no espaço tridimensional e sua representação no espaço bidimensional.",
    "H_7": "H7 - Identificar características de figuras planas ou espaciais.",
    "H_8": "H8 - Resolver situação-problema que envolva conhecimentos geométricos de espaço e forma.",
    "H_9": "H9 - Utilizar conhecimentos geométricos de espaço e forma na seleção de argumentos propostos como solução de problemas do cotidiano.",
    "H_10": "H10 - Identificar relações entre grandezas e unidades de medida.",
    "H_11": "H11 - Utilizar a noção de escalas na leitura de representação de situação do cotidiano.",
    "H_12": "H12 - Resolver situação-problema que envolva medidas de grandezas.",
    "H_13": "H13 - Avaliar o resultado de uma medição na construção de um argumento consistente.",
    "H_14": "H14 - Avaliar proposta de intervenção na realidade utilizando conhecimentos geométricos relacionados a grandezas e medidas.",
    "H_15": "H15 - Identificar a relação de dependência entre grandezas.",
    "H_16": "H16 - Resolver situação-problema envolvendo a variação de grandezas, direta ou inversamente proporcionais.",
    "H_17": "H17 - Analisar informações envolvendo a variação de grandezas como recurso para a construção de argumentação.",
    "H_18": "H18 - Avaliar propostas de intervenção na realidade envolvendo variação de grandezas.",
    "H_19": "H19 - Identificar representações algébricas que expressem a relação entre grandezas.",
    "H_20": "H20 - Interpretar gráfico cartesiano que represente relações entre grandezas.",
    "H_21": "H21 - Resolver situação-problema cuja modelagem envolva conhecimentos algébricos.",
    "H_22": "H22 - Utilizar conhecimentos algébricos/geométricos como recurso para a construção de argumentação.",
    "H_23": "H23 - Avaliar propostas de intervenção na realidade utilizando conhecimentos algébricos.",
    "H_24": "H24 - Utilizar informações expressas em gráficos ou tabelas para fazer inferências.",
    "H_25": "H25 - Resolver problema com dados apresentados em tabelas ou gráficos.",
    "H_26": "H26 - Analisar informações expressas em gráficos ou tabelas como recurso para a construção de argumentos.",
    "H_27": "H27 - Calcular medidas de tendência central ou de dispersão de um conjunto de dados expressos em uma tabela de frequências de dados agrupados (não em classes) ou em gráficos.",
    "H_28": "H28 - Resolver situação-problema que envolva conhecimentos de estatística e probabilidade.",
    "H_29": "H29 - Utilizar conhecimentos de estatística e probabilidade como recurso para a construção de argumentação.",
    "H_30": "H30 - Avaliar propostas de intervenção na realidade utilizando conhecimentos de estatística e probabilidade"
}

# ==============================================================================
# EXTRAÇÃO DIRETA E SIMPLES DO GABARITO DA PROVA 1407
# ==============================================================================
@st.cache_data
def extrair_gabarito_1407():
    caminho_resultados = os.path.join(PASTA_DADOS, 'RESULTADOS_2024.csv')
    
    if not os.path.exists(caminho_resultados):
        st.error(f"❌ Arquivo não encontrado em: {caminho_resultados}")
        return {}, []

    # Matemática no ENEM 2024 vai da questão 136 até a 180
    lista_questoes = list(range(136, 181))
    gabarito_mapeado = {}

    # Lê o arquivo por partes e pega o gabarito do primeiro que fez a prova 1407
    with pd.read_csv(caminho_resultados, encoding='latin1', sep=';', chunksize=5000) as reader:
        for chunk in reader:
            linha_alvo = chunk[chunk['CO_PROVA_MT'] == 1407]
            
            if not linha_alvo.empty:
                str_gabarito = str(linha_alvo['TX_GABARITO_MT'].iloc[0])
                
                # Associa a string (45 caracteres) com a numeração (136 a 180)
                gabarito_mapeado = {q: letra for q, letra in zip(lista_questoes, str_gabarito)}
                break
                
    return gabarito_mapeado, lista_questoes

# Executa a extração
gabarito_oficial, lista_questoes = extrair_gabarito_1407()

# ==========================================
# CARREGAMENTO DO MODELO BAYESIANO (.PKL)
# ==========================================
@st.cache_resource
def carregar_dados_rede():
    caminho_pkl = 'modelo_enem_rede.pkl'
    if os.path.exists(caminho_pkl):
        with open(caminho_pkl, 'rb') as f:
            dados = pickle.load(f)
            if isinstance(dados, dict):
                return dados['model']
            return dados
    return None

model = carregar_dados_rede()

if model is None:
    st.error("❌ Arquivo 'modelo_enem_rede.pkl' não encontrado!")
    st.info("Execute o script de treinamento primeiro para gerar os parâmetros da rede.")
    st.stop()

# ==========================================
# INTERFACE STREAMLIT
# ==========================================
st.title("🧠 Portal do Aluno: Avaliação via Rede Bayesiana (Matemática ENEM)")
st.markdown("Insira suas marcações das questões de **Matemática** e veja a IA inferir seu domínio de habilidades latentes.")

col_prova, col_respostas = st.columns([1, 1])

# --- COLUNA DA ESQUERDA: Exibição da Prova ---
with col_prova:
    st.subheader("📄 Caderno de Questões (Dia 2 - Azul)")
    CAMINHO_PDF = r"C:\Users\Lucas\Documents\UFMG\Iniciação Científica\Aprendizagem\ENEM2024DADOS\PROVAS E GABARITOS\ENEM_2024_P2_CAD_07_DIA_2_AZUL.pdf"
    
    if os.path.exists(CAMINHO_PDF):
        with open(CAMINHO_PDF, "rb") as f:
            base64_pdf = base64.b64encode(f.read()).decode('utf-8')
        pdf_html = f'<embed src="data:application/pdf;base64,{base64_pdf}" width="100%" height="800px" type="application/pdf"></embed>'
        st.markdown(pdf_html, unsafe_allow_html=True)
    else:
        st.warning(f"⚠️ Arquivo PDF não encontrado em: {CAMINHO_PDF}")

# --- COLUNA DA DIREITA: Entrada de Dados e Inferência ---
with col_respostas:
    st.subheader("🖋️ Cartão de Respostas Interativo (Matemática - Caderno Azul)")
    
    # Menu retrátil para auditar visualmente se o gabarito extraído bate com a sua folha física
    with st.expander("🔍 Conferir Gabarito Oficial"):
        st.write(gabarito_oficial)

    with st.form("gabarito_aluno"):
        respostas_aluno = {}
        st.write("Marque as alternativas assinaladas:")
        
        # Ordenação garantida (Questões 136 a 180 em ordem crescente)
        for q in sorted(lista_questoes):
            respostas_aluno[q] = st.radio(
                f"Questão {q}:",
                ('Não Respondida', 'A', 'B', 'C', 'D', 'E'),
                horizontal=True,
                key=f"q_{q}"
            )
            
        st.markdown("---")
        botao_enviar = st.form_submit_button("Submeter Respostas para Análise Probabilística")

    # Processamento pós-submissão
    if botao_enviar:
        evidencias_rede = {}
        total_respondidas = 0
        acertos = 0
        
        for q, resp in respostas_aluno.items():
            if resp != 'Não Respondida':
                total_respondidas += 1
                gabarito_correto = gabarito_oficial.get(q, "X")
                acertou = 1 if resp == gabarito_correto else 0
                if acertou == 1:
                    acertos += 1
                
                # Alinha a evidência com o nome dos nós da pgmpy (ex: Item_136)
                evidencias_rede[f"Item_{q}"] = acertou

        if total_respondidas == 0:
            st.warning("Por favor, preencha o gabarito antes de processar.")
        else:
            st.markdown("### 📊 Resultado Geral")
            st.metric(
                label="Total de Acertos em Matemática", 
                value=f"{acertos} / {total_respondidas}", 
                delta=f"Aproveitamento: {(acertos/total_respondidas)*100:.1f}%"
            )
            
            st.markdown("---")
            st.markdown("### 🔮 Diagnóstico de Habilidades Latentes (Inferência IA)")
            
            try:
                inference = VariableElimination(model)
                nos_habilidade = [node for node in model.nodes() if str(node).startswith('H_')]
                
                perfil_diagnostico = []
                for hab in sorted(nos_habilidade, key=lambda x: int(x.split('_')[1])):
                    resultado = inference.query(variables=[hab], evidence=evidencias_rede, show_progress=False)
                    prob_dominio = resultado.values[1] 
                    perfil_diagnostico.append({'Habilidade': hab, 'Probabilidade': prob_dominio})
                
                df_diagnostico = pd.DataFrame(perfil_diagnostico)
                
                # Exibição atualizada mapeando o nome correto
                for _, row in df_diagnostico.iterrows():
                    hab_key = row['Habilidade']
                    # Busca a descrição no dicionário; se não achar, exibe a própria chave (ex: H_1)
                    hab_texto = DESCRICOES_HABILIDADES.get(hab_key, hab_key)
                    
                    st.write(f"**{hab_texto}**")
                    st.progress(float(row['Probabilidade']))
                    st.caption(f"Probabilidade de domínio real: **{row['Probabilidade']:.2%}**")
                    
            except Exception as error:
                st.error(f"Erro na inferência da rede pgmpy: {error}")