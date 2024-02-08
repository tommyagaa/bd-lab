PGDMP     &    $                {            progetto    15.3 (Debian 15.3-1.pgdg120+1)    15.5 +    N           0    0    ENCODING    ENCODING        SET client_encoding = 'UTF8';
                      false            O           0    0 
   STDSTRINGS 
   STDSTRINGS     (   SET standard_conforming_strings = 'on';
                      false            P           0    0 
   SEARCHPATH 
   SEARCHPATH     8   SELECT pg_catalog.set_config('search_path', '', false);
                      false            Q           1262    16451    progetto    DATABASE     s   CREATE DATABASE progetto WITH TEMPLATE = template0 ENCODING = 'UTF8' LOCALE_PROVIDER = libc LOCALE = 'en_US.utf8';
    DROP DATABASE progetto;
                tomas    false                        2615    16506    progetto    SCHEMA        CREATE SCHEMA progetto;
    DROP SCHEMA progetto;
                tomas    false            �            1255    16601 #   carriera_completa_studente(integer)    FUNCTION     �  CREATE FUNCTION progetto.carriera_completa_studente(matricola_input integer) RETURNS TABLE(codice_ins integer, nomeins character varying, voto integer, data_svolgimento date)
    LANGUAGE plpgsql
    AS $$
BEGIN
    RETURN QUERY
    SELECT 
        me.codice_ins,
        i.nome AS nomeIns,
        me.voto,
        me.data_svolgimento
    FROM memoria_esame me
    JOIN insegnamenti i ON me.codice_ins = i.codice
    WHERE me.matricola = matricola_input;
END;
$$;
 L   DROP FUNCTION progetto.carriera_completa_studente(matricola_input integer);
       progetto          tomas    false    6            �            1255    16602 !   carriera_valida_studente(integer)    FUNCTION     �  CREATE FUNCTION progetto.carriera_valida_studente(matricola_input integer) RETURNS TABLE(codice_ins integer, nomeins character varying, voto integer, data_svolgimento date)
    LANGUAGE plpgsql
    AS $$
begin
    return QUERY
    select 
        e.codiceIns,
        i.nome AS nomeIns,
        e.voto,
        e.data_svolgimento
    from esame e
    join insegnamenti i on e.codice_ins = i.codice
    where e.matricola = matricola_input and e.voto >= 18;
end;
$$;
 J   DROP FUNCTION progetto.carriera_valida_studente(matricola_input integer);
       progetto          tomas    false    6            �            1255    16603    cdl_info(integer)    FUNCTION     -  CREATE FUNCTION progetto.cdl_info(codice_corso integer) RETURNS TABLE(codiceins integer, nomeins character varying, descrizione character varying, annoins integer, nomedocente character varying, cognomedocente character varying)
    LANGUAGE plpgsql
    AS $$
begin
    return QUERY
    select 
        i.codice as codiceIns,
        i.nome as nomeIns,
        i.descrizione as descrizione,
        i.anno as annoIns,
        d.nome as nomeDocente,
        d.cognome as cognomeDocente
    from insegnamenti i
    where i.cdl_codice = codice_corso;
end;
$$;
 7   DROP FUNCTION progetto.cdl_info(codice_corso integer);
       progetto          tomas    false    6            �            1255    16594    check_date_exam()    FUNCTION     �  CREATE FUNCTION progetto.check_date_exam() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    anno_insegnamento INTEGER;
BEGIN
    -- Ottenere l'anno accademico associato all'insegnamento
    SELECT i.anno INTO anno_insegnamento
    FROM progetto.insegnamenti i
    WHERE i.codice = NEW.id;

    -- Controllare se esiste già un appello per la stessa data e anno accademico
    IF EXISTS (
        SELECT 1
        FROM progetto.appelli a
        INNER JOIN progetto.insegnamenti i ON i.codice = a.id
        WHERE NEW.data_svolgimento = a.data_svolgimento
        AND anno_insegnamento = i.anno
    ) THEN
        RAISE EXCEPTION 'Esiste già un appello per la stessa data e anno accademico';
    END IF;

    RETURN NEW;
END;
$$;
 *   DROP FUNCTION progetto.check_date_exam();
       progetto          tomas    false    6            �            1255    16715 !   salvataggio_studente_in_memoria()    FUNCTION     �  CREATE FUNCTION progetto.salvataggio_studente_in_memoria() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    -- Inserisci lo studente nella tabella memoria_studente
    INSERT INTO progetto.memoria_studente(matricola, nome, cognome, email)
    VALUES (OLD.matricola, OLD.nome, OLD.cognome, OLD.email);

    -- Controlla se siamo chiamati da DELETE
    IF TG_OP = 'DELETE' THEN
        RETURN OLD;
    END IF;

    -- Elimina lo studente dalla tabella studente
    DELETE FROM progetto.studente WHERE matricola = OLD.matricola;

    RETURN NULL;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Errore durante l''inserimento in memoria_studente: %', SQLERRM;
        RETURN NULL;
END;
$$;
 :   DROP FUNCTION progetto.salvataggio_studente_in_memoria();
       progetto          tomas    false    6            �            1259    16575    appelli    TABLE     v   CREATE TABLE progetto.appelli (
    data_svolgimento date NOT NULL,
    id integer,
    iscritto character varying
);
    DROP TABLE progetto.appelli;
       progetto         heap    tomas    false    6            �            1259    16517    corso_di_laurea    TABLE     �   CREATE TABLE progetto.corso_di_laurea (
    codice integer NOT NULL,
    livello character varying(10) NOT NULL,
    nome character varying(20) NOT NULL
);
 %   DROP TABLE progetto.corso_di_laurea;
       progetto         heap    tomas    false    6            �            1259    16512    docente    TABLE     �   CREATE TABLE progetto.docente (
    email character varying(25) NOT NULL,
    nome character varying(10) NOT NULL,
    cognome character varying(10) NOT NULL,
    password character varying(255) NOT NULL
);
    DROP TABLE progetto.docente;
       progetto         heap    tomas    false    6            R           0    0    COLUMN docente.password    ACL     ?   GRANT REFERENCES(password) ON TABLE progetto.docente TO tomas;
          progetto          tomas    false    216            �            1259    16547    esame    TABLE     �   CREATE TABLE progetto.esame (
    voto integer NOT NULL,
    codice_cdl integer NOT NULL,
    codice_ins integer NOT NULL,
    data_svolgimento date NOT NULL,
    studente character varying
);
    DROP TABLE progetto.esame;
       progetto         heap    tomas    false    6            �            1259    16532    insegnamenti    TABLE     �   CREATE TABLE progetto.insegnamenti (
    codice integer NOT NULL,
    cdl_codice integer,
    nome character varying(15) NOT NULL,
    anno integer NOT NULL,
    descrizione character varying(100) NOT NULL,
    email_docente character varying
);
 "   DROP TABLE progetto.insegnamenti;
       progetto         heap    tomas    false    6            �            1259    16562    memoria_esame    TABLE     �   CREATE TABLE progetto.memoria_esame (
    voto integer NOT NULL,
    nome character varying(50) NOT NULL,
    studente integer,
    codice_ins integer NOT NULL
);
 #   DROP TABLE progetto.memoria_esame;
       progetto         heap    tomas    false    6            �            1259    16697    memoria_studente    TABLE     �   CREATE TABLE progetto.memoria_studente (
    matricola integer NOT NULL,
    nome character varying(10) NOT NULL,
    cognome character varying(10) NOT NULL,
    email character varying NOT NULL,
    cdl_codice integer
);
 &   DROP TABLE progetto.memoria_studente;
       progetto         heap    tomas    false    6            �            1259    16507 
   segreteria    TABLE     |   CREATE TABLE progetto.segreteria (
    email character varying(25) NOT NULL,
    password character varying(20) NOT NULL
);
     DROP TABLE progetto.segreteria;
       progetto         heap    tomas    false    6            S           0    0    COLUMN segreteria.password    ACL     B   GRANT REFERENCES(password) ON TABLE progetto.segreteria TO tomas;
          progetto          tomas    false    215            �            1259    16522    studente    TABLE       CREATE TABLE progetto.studente (
    matricola integer NOT NULL,
    nome character varying(10) NOT NULL,
    cognome character varying(10) NOT NULL,
    email character varying(25) NOT NULL,
    password character varying(20) NOT NULL,
    cdl_codice integer
);
    DROP TABLE progetto.studente;
       progetto         heap    tomas    false    6            T           0    0    COLUMN studente.password    ACL     @   GRANT REFERENCES(password) ON TABLE progetto.studente TO tomas;
          progetto          tomas    false    218            J          0    16575    appelli 
   TABLE DATA                 progetto          tomas    false    222   m8       E          0    16517    corso_di_laurea 
   TABLE DATA                 progetto          tomas    false    217   �8       D          0    16512    docente 
   TABLE DATA                 progetto          tomas    false    216   �9       H          0    16547    esame 
   TABLE DATA                 progetto          tomas    false    220   �:       G          0    16532    insegnamenti 
   TABLE DATA                 progetto          tomas    false    219   C;       I          0    16562    memoria_esame 
   TABLE DATA                 progetto          tomas    false    221   �<       K          0    16697    memoria_studente 
   TABLE DATA                 progetto          tomas    false    223   -=       C          0    16507 
   segreteria 
   TABLE DATA                 progetto          tomas    false    215   �=       F          0    16522    studente 
   TABLE DATA                 progetto          tomas    false    218   z>       �           2606    16521 $   corso_di_laurea corso_di_laurea_pkey 
   CONSTRAINT     h   ALTER TABLE ONLY progetto.corso_di_laurea
    ADD CONSTRAINT corso_di_laurea_pkey PRIMARY KEY (codice);
 P   ALTER TABLE ONLY progetto.corso_di_laurea DROP CONSTRAINT corso_di_laurea_pkey;
       progetto            tomas    false    217            �           2606    16516    docente docente_pkey 
   CONSTRAINT     W   ALTER TABLE ONLY progetto.docente
    ADD CONSTRAINT docente_pkey PRIMARY KEY (email);
 @   ALTER TABLE ONLY progetto.docente DROP CONSTRAINT docente_pkey;
       progetto            tomas    false    216            �           2606    16551    esame esame_pkey 
   CONSTRAINT     d   ALTER TABLE ONLY progetto.esame
    ADD CONSTRAINT esame_pkey PRIMARY KEY (codice_cdl, codice_ins);
 <   ALTER TABLE ONLY progetto.esame DROP CONSTRAINT esame_pkey;
       progetto            tomas    false    220    220            �           2606    16536    insegnamenti insegnamenti_pkey 
   CONSTRAINT     b   ALTER TABLE ONLY progetto.insegnamenti
    ADD CONSTRAINT insegnamenti_pkey PRIMARY KEY (codice);
 J   ALTER TABLE ONLY progetto.insegnamenti DROP CONSTRAINT insegnamenti_pkey;
       progetto            tomas    false    219            �           2606    16511    segreteria segreteria_pkey 
   CONSTRAINT     ]   ALTER TABLE ONLY progetto.segreteria
    ADD CONSTRAINT segreteria_pkey PRIMARY KEY (email);
 F   ALTER TABLE ONLY progetto.segreteria DROP CONSTRAINT segreteria_pkey;
       progetto            tomas    false    215            �           1259    16672    studente_pkey    INDEX     P   CREATE UNIQUE INDEX studente_pkey ON progetto.studente USING btree (matricola);
 #   DROP INDEX progetto.studente_pkey;
       progetto            tomas    false            �           2620    16729    appelli check_date_exam_trigger    TRIGGER     �   CREATE TRIGGER check_date_exam_trigger BEFORE INSERT ON progetto.appelli FOR EACH ROW EXECUTE FUNCTION progetto.check_date_exam();
 :   DROP TRIGGER check_date_exam_trigger ON progetto.appelli;
       progetto          tomas    false    239    222            �           2620    16718    studente delete_studente    TRIGGER     �   CREATE TRIGGER delete_studente BEFORE DELETE ON progetto.studente FOR EACH ROW EXECUTE FUNCTION progetto.salvataggio_studente_in_memoria();
 3   DROP TRIGGER delete_studente ON progetto.studente;
       progetto          tomas    false    218    238            �           2606    16552    esame esame_codice_cdl_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY progetto.esame
    ADD CONSTRAINT esame_codice_cdl_fkey FOREIGN KEY (codice_cdl) REFERENCES progetto.corso_di_laurea(codice);
 G   ALTER TABLE ONLY progetto.esame DROP CONSTRAINT esame_codice_cdl_fkey;
       progetto          tomas    false    220    3241    217            �           2606    16557    esame esame_codice_ins_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY progetto.esame
    ADD CONSTRAINT esame_codice_ins_fkey FOREIGN KEY (codice_ins) REFERENCES progetto.insegnamenti(codice);
 G   ALTER TABLE ONLY progetto.esame DROP CONSTRAINT esame_codice_ins_fkey;
       progetto          tomas    false    219    3244    220            �           2606    16649 )   insegnamenti insegnamenti_cdl_codice_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY progetto.insegnamenti
    ADD CONSTRAINT insegnamenti_cdl_codice_fkey FOREIGN KEY (cdl_codice) REFERENCES progetto.corso_di_laurea(codice);
 U   ALTER TABLE ONLY progetto.insegnamenti DROP CONSTRAINT insegnamenti_cdl_codice_fkey;
       progetto          tomas    false    3241    217    219            �           2606    16664 *   insegnamenti insegnamenti_cdl_codice_fkey1    FK CONSTRAINT     �   ALTER TABLE ONLY progetto.insegnamenti
    ADD CONSTRAINT insegnamenti_cdl_codice_fkey1 FOREIGN KEY (cdl_codice) REFERENCES progetto.corso_di_laurea(codice);
 V   ALTER TABLE ONLY progetto.insegnamenti DROP CONSTRAINT insegnamenti_cdl_codice_fkey1;
       progetto          tomas    false    3241    219    217            J   r   x���v
Q���W((�OO-)��K,(H���Ts�	uV�P7202�54�52R�Q062�QP��Q״��$V�������!���t�tA�[���j��nKKR5��n���� l�Q�      E   �   x���=�0@���lU�����B�`յ��6�K*�_o�v���x��$�7��H��^<Y�h�J�FW*P3�8���&��d B˄J�C7$ꦹK��*H|h�Y�ߩ����zUP���C�nl� $)4�M��~[p����ˎ`�\�ʚ�6�������MZ��΢vi.��F�?5[z�#�n��j�����%�{� � ���      D   �   x����
�0��>E�*HQ-V)��gzJ 6�D�<�&V-q�n����H�*�͖��\�<�12)%�� �O��� �x��b����.n��O��G��K��~�Gy+�\=@�P���E�P�+RPF
yFI���Sy���?�������R5�ß�L�!��.P�&yԞ<�5�YȆ��CQ��}]EwZ>�m      H   �   x���v
Q���W((�OO-)��K-N�MUs�	uV�02�Q02�Q0���QP7202�50�54T�|��Ssr�}3�KJSR�JR�2K�5��<	l5�� b����������B�0�lv0�`��ļ�|�"tc�� �PK      G   M  x���OK�@���s�B)�-�ŋQ��Gl�>&cHvew����D�T�X�=����۷������u��Wg
�N�x*Vd�S�|\l�d6��A�1D�C_��lE��tm���3�Nr*AS��b]��3�[Q\���]�6'�p�N/F��D��iC�e��!L#��2kr�43�ҬP\�ɪ,�3( ��ApJ�U�%�Y��!�X��\���1�]io��U{��$�[`��G�E^⛣-����/�j���>�U���2���WK�������^��R�(gg2i�
4߬C��j����S?����U�v��k�Ⲡg���2,�:�9���Y�w�      I   }   x���v
Q���W((�OO-)���M��/�L�O-N�MUs�	uV�02�QP�M,JN�����t(.)MI�+I��,Q�Q����Q0��Դ��$�Pc"�42 �L#S��nE�y��nEX��0 �� ۨO�      K   �   x���O�@��~��mA��E�<j���1�b�����[�/�4�y?�$yI��J^J�\k����J��������u\�M��Ћ����Hj����S��*��|�
mVy��ۓ��E�=�MD-{��Z�:1�Y��50,Yh��P�DtV
�
ĔuXo�O3I�|���_F17/����q�7�S��      C   o   x���v
Q���W((�OO-)��+NM/J-I-�LTs�	uV�P��/Jͫ��Mu@��e���(�C�2��5��<�5181/�(1(���@c����K����B�y\\ ӉQ�      F   �   x���Kk�0������bYrUriI1�)8i�J���2z\���-4)�͎X̎��n]�I�ݿ���圞Y�O�u��-�_�;r�3^ܑ��樚F�A����F���is.�L#ǁ��ERF�2�	����`�z�Y�o��J^
�"�SxO��:5��L�����|g`�D|8�3�ƫ�ijm-LwԵ����y4D���Q[:>��ƻ��.g
���H|^܇^�l�L����wc���9������     