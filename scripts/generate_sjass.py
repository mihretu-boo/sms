#!/usr/bin/env python3
"""
SJASS Student Roster & Report Card Generator
Shalaka Jatani Ali Secondary School – Oromia / Borana / Yabello

Usage:
  python3 generate_sjass.py                              # sample data, cwd
  python3 generate_sjass.py --data /tmp/data.json       # DB data from PHP
  python3 generate_sjass.py --output /tmp/out.xlsx      # custom output path
  python3 generate_sjass.py --grade 9                   # single grade only
"""
import argparse
import json
import os
import sys

import openpyxl
from openpyxl.styles import PatternFill, Font, Alignment, Border, Side
from openpyxl.utils import get_column_letter
from openpyxl.worksheet.datavalidation import DataValidation
from openpyxl.formatting.rule import ColorScaleRule, FormulaRule
from openpyxl.worksheet.page import PageMargins

# ─────────────────────────────────────────────────────────────
# DEFAULTS (overridden by --data JSON when called from PHP)
# ─────────────────────────────────────────────────────────────
SCHOOL_EN  = "Shalaka Jatani Ali Secondary School"
SCHOOL_OM  = "Mana Barumsaa Sadarkaa 2ffaa Shalaka Jatani Ali"
REGION     = "Oromia Regional State"
ZONE       = "Borana Zone"
WOREDA     = "Yabello"
PRINCIPAL  = "Ato [Principal Name]"
AY_EC      = "2018"
AY_GC      = "2025/26"
PASS_MARK  = 50
N_STU      = 40        # student slots per grade

DATA_START = 5         # first data row (rows 1-4 = headers)

# ─── Colour palette ───────────────────────────────────────────
GD = "1A5E20"; GM = "2E7D32"; GL = "E8F5E9"
GO = "F9A825"; GL2= "FFF8E1"; BI = "DBEAFE"
WH = "FFFFFF"; GY = "F5F5F5"; GB = "BDBDBD"

fHdr  = PatternFill("solid", fgColor=GD)
fSub  = PatternFill("solid", fgColor=GM)
fAlt  = PatternFill("solid", fgColor=GL)
fGold = PatternFill("solid", fgColor=GO)
fGoldL= PatternFill("solid", fgColor=GL2)
fIn   = PatternFill("solid", fgColor=BI)
fWht  = PatternFill("solid", fgColor=WH)
fGray = PatternFill("solid", fgColor=GY)

fT  = Font(name="Calibri", bold=True, size=14, color=WH)
fSH = Font(name="Calibri", bold=True, size=11, color=WH)
fH  = Font(name="Calibri", bold=True, size=10, color=WH)
fH8 = Font(name="Calibri", bold=True, size=8,  color=WH)
fB  = Font(name="Calibri", size=10)
fBo = Font(name="Calibri", bold=True, size=10)
fS  = Font(name="Calibri", size=9)
fSB = Font(name="Calibri", bold=True, size=9)
fLk = Font(name="Calibri", size=10, color="1155CC", underline="single")

aC   = Alignment(horizontal="center", vertical="center", wrap_text=True)
aL   = Alignment(horizontal="left",   vertical="center", wrap_text=True)
aRot = Alignment(horizontal="center", vertical="bottom", text_rotation=90, wrap_text=True)

_t = Side(style="thin",   color=GB)
_m = Side(style="medium", color="757575")
_g = Side(style="medium", color=GD)
bT = Border(left=_t, right=_t, top=_t, bottom=_t)

# ─── Subjects ─────────────────────────────────────────────────
SUBJ_9_10 = [
    ("Afaan Oromoo",     "Afan Oromo"),
    ("Amaariffaa",       "Amharic"),
    ("Ingiliizii",       "English"),
    ("Herrega",          "Math"),
    ("Fisikii",          "Physics"),
    ("Keemii",           "Chemistry"),
    ("Bayoloojii",       "Biology"),
    ("Joogirafii",       "Geography"),
    ("Seenaa",           "History"),
    ("Lammummaa",        "Citizenship"),
    ("TM / IT",          "IT"),
    ("HPE",              "HPE"),
]
SUBJ_11_12 = [
    ("Af.Or / Amaariffaa","Afan Or./Amh."),
    ("Ingiliizii",        "English"),
    ("Herrega",           "Math"),
    ("Fisikii (NS)",      "Physics (NS)"),
    ("Keemii (NS)",       "Chemistry (NS)"),
    ("Bayoloojii (NS)",   "Biology (NS)"),
    ("Joogirafii (SS)",   "Geography (SS)"),
    ("Seenaa (SS)",       "History (SS)"),
    ("Dinagdee (SS)",     "Economics (SS)"),
    ("TM / IT",           "IT"),
]

# ─── Built-in sample data (fallback when no --data JSON) ──────
SAMPLE_STUDENTS = {
    9:  [("Lemi","Godana Guyo","M",15,"A","GP"),
         ("Fatuma","Boru Hassan","F",14,"A","GP"),
         ("Diriba","Jira Wario","M",16,"B","GP"),
         ("Birhane","Guta Amante","F",15,"B","GP"),
         ("Kalif","Dida Roba","M",15,"C","GP")],
    10: [("Asefa","Bule Gosa","M",16,"A","GP"),
         ("Caaltuu","Banti Dida","F",16,"A","GP"),
         ("Galgalo","Wario Jatani","M",17,"B","GP"),
         ("Naima","Hassan Roba","F",15,"B","GP"),
         ("Tolera","Gurmessa Jira","M",16,"C","GP")],
    11: [("Abduba","Golicha Tuke","M",17,"A","NS"),
         ("Hidda","Bule Gosa","F",17,"A","NS"),
         ("Boru","Halake Galma","M",18,"A","SS"),
         ("Shukri","Dido Wario","F",17,"A","SS"),
         ("Kumera","Jatani Guyo","M",17,"B","NS")],
    12: [("Wakjira","Boru Dida","M",18,"A","NS"),
         ("Falmata","Halake Jima","F",18,"A","NS"),
         ("Guyyo","Roba Gurmessa","M",19,"A","SS"),
         ("Iftu","Wario Dida","F",18,"A","SS"),
         ("Desta","Golicha Amante","M",18,"B","NS")],
}

# ─────────────────────────────────────────────────────────────
# HELPERS
# ─────────────────────────────────────────────────────────────
def cl(n): return get_column_letter(n)

def sc(ws, row, col, val=None, font=None, fill=None, align=None, border=None, fmt=None):
    c = ws.cell(row=row, column=col)
    if val   is not None: c.value         = val
    if font  is not None: c.font          = font
    if fill  is not None: c.fill          = fill
    if align is not None: c.alignment     = align
    if border is not None: c.border       = border
    if fmt   is not None: c.number_format = fmt
    return c

def mg(ws, r1, c1, r2, c2, val=None, font=None, fill=None, align=None):
    ws.merge_cells(start_row=r1, start_column=c1, end_row=r2, end_column=c2)
    c = ws.cell(row=r1, column=c1)
    if val   is not None: c.value     = val
    if font  is not None: c.font      = font
    if fill  is not None: c.fill      = fill
    if align is not None: c.alignment = align
    for r in range(r1, r2+1):
        for cc in range(c1, c2+1):
            ws.cell(row=r, column=cc).border = bT
    return c

def print_landscape(ws):
    ws.page_setup.orientation = "landscape"
    ws.page_setup.paperSize   = 9
    ws.page_setup.fitToPage   = True
    ws.page_setup.fitToWidth  = 1
    ws.page_setup.fitToHeight = 0
    ws.page_margins = PageMargins(left=0.39, right=0.39, top=0.47, bottom=0.47,
                                  header=0.28, footer=0.28)

def print_portrait(ws):
    ws.page_setup.orientation = "portrait"
    ws.page_setup.paperSize   = 9
    ws.page_setup.fitToPage   = True
    ws.page_setup.fitToWidth  = 1
    ws.page_setup.fitToHeight = 0
    ws.page_margins = PageMargins(left=0.39, right=0.39, top=0.47, bottom=0.47,
                                  header=0.28, footer=0.28)

# ─────────────────────────────────────────────────────────────
# ROSTER  (Ros9 / Ros10 / Ros11 / Ros12)
# ─────────────────────────────────────────────────────────────
def build_roster(wb, grade, stu_list, cfg):
    school_en = cfg.get('school', SCHOOL_EN)
    school_om = cfg.get('school_om', SCHOOL_OM)
    ay_ec     = cfg.get('ay_ec', AY_EC)
    ay_gc     = cfg.get('ay_gc', AY_GC)
    pass_mark = cfg.get('pass_mark', PASS_MARK)

    ws = wb.create_sheet(f"Ros{grade}")
    is_senior = grade in (11, 12)
    subjects  = SUBJ_11_12 if is_senior else SUBJ_9_10
    nsub      = len(subjects)

    # Column layout
    if is_senior:
        COL_STR  = 6; COL_SEM  = 7; COL_SUBJ0 = 8
    else:
        COL_STR  = None; COL_SEM = 6; COL_SUBJ0 = 7

    COL_NO=1; COL_NAME=2; COL_SEX=3; COL_AGE=4; COL_SEC=5
    COL_SUBJ_LAST = COL_SUBJ0 + nsub - 1
    COL_TOT  = COL_SUBJ_LAST + 1
    COL_AVG  = COL_TOT + 1
    COL_RANK = COL_AVG + 1
    COL_RES  = COL_RANK + 1
    COL_RMK  = COL_RES + 1
    LAST_COL = COL_RMK

    sc_l  = cl(COL_SUBJ0); ec_l = cl(COL_SUBJ_LAST)
    tot_l = cl(COL_TOT);   avg_l= cl(COL_AVG)
    rnk_l = cl(COL_RANK);  res_l= cl(COL_RES)

    # Cap N_STU to max of (actual students, N_STU)
    n_rows = max(len(stu_list), N_STU)
    last_av_row = DATA_START + (n_rows - 1) * 3 + 2

    # ── Row 1: banner ────────────────────────────────────────
    mg(ws,1,1,1,LAST_COL,
       val=f"★  {school_en}  ·  {school_om}  ★",
       font=fT, fill=fHdr, align=aC)
    ws.row_dimensions[1].height = 30

    # ── Row 2: grade / year ───────────────────────────────────
    mg(ws,2,1,2,LAST_COL,
       val=(f"Kutaa/Grade {grade}  ·  Bara Barnootaa: {ay_ec} EC / {ay_gc} GC  "
            f"·  {ZONE}, {REGION}  ·  Pass Mark = {pass_mark}%"),
       font=Font(name="Calibri", bold=True, size=10, color=WH),
       fill=fSub, align=aC)
    ws.row_dimensions[2].height = 22

    # ── Rows 3-4: column headers ──────────────────────────────
    def hcell(r1,c1,r2,c2,val,fl=fHdr,fnt=fH):
        mg(ws,r1,c1,r2,c2,val=val,font=fnt,fill=fl,align=aC)

    id_hdrs = ["Lak.\nNo","Maqaa Barataa / Student Name","Saala\nSex","Umurii\nAge","Kutaa\nSection"]
    for i,h in enumerate(id_hdrs,1):
        hcell(3,i,4,i,h)
    if is_senior:
        hcell(3,COL_STR,4,COL_STR,"Damee\nStream")
        hcell(3,COL_SEM,4,COL_SEM,"Sem.")
    else:
        hcell(3,COL_SEM,4,COL_SEM,"Sem.")

    hcell(3,COL_SUBJ0,3,COL_SUBJ_LAST,
          "ADEEMSA BARNOOTAA / SUBJECT SCORES", fnt=fH8)
    for i,(om,en) in enumerate(subjects):
        col = COL_SUBJ0 + i
        c = ws.cell(row=4, column=col)
        c.value     = f"{om}\n{en}"
        c.font      = Font(name="Calibri", bold=True, size=8, color=WH)
        c.fill      = fHdr; c.alignment = aRot; c.border = bT

    hcell(3,COL_TOT,4,COL_TOT,   "Waligala\nTotal")
    hcell(3,COL_AVG,4,COL_AVG,   "Gidgala\nAverage",
          fl=fGold, fnt=Font(name="Calibri",bold=True,size=10,color="3E2600"))
    hcell(3,COL_RANK,4,COL_RANK, "Sadarkaa\nRank")
    hcell(3,COL_RES,4,COL_RES,   "Bu'aa\nResult")
    hcell(3,COL_RMK,4,COL_RMK,   "Yaada\nRemark")

    ws.row_dimensions[3].height = 34
    ws.row_dimensions[4].height = 72

    # ── Column widths ─────────────────────────────────────────
    ws.column_dimensions["A"].width = 5
    ws.column_dimensions["B"].width = 24
    ws.column_dimensions["C"].width = 6
    ws.column_dimensions["D"].width = 6
    ws.column_dimensions["E"].width = 8
    if is_senior:
        ws.column_dimensions["F"].width = 7
        ws.column_dimensions["G"].width = 6
    else:
        ws.column_dimensions["F"].width = 6
    for i in range(nsub):
        ws.column_dimensions[cl(COL_SUBJ0+i)].width = 7
    ws.column_dimensions[cl(COL_TOT)].width  = 9
    ws.column_dimensions[cl(COL_AVG)].width  = 9
    ws.column_dimensions[cl(COL_RANK)].width = 7
    ws.column_dimensions[cl(COL_RES)].width  = 18
    ws.column_dimensions[cl(COL_RMK)].width  = 18

    # ── Data rows ─────────────────────────────────────────────
    for n in range(n_rows):
        r1 = DATA_START + n * 3
        r2 = r1 + 1
        r3 = r1 + 2
        alt_fill = fAlt if (n % 2 == 1) else fWht

        ws.row_dimensions[r1].height = 15
        ws.row_dimensions[r2].height = 15
        ws.row_dimensions[r3].height = 16

        stu = stu_list[n] if n < len(stu_list) else None
        # stu = (first, last, sex, age, section, stream)

        mg(ws,r1,COL_NO,  r3,COL_NO,   val=n+1, font=fBo, fill=alt_fill, align=aC)
        mg(ws,r1,COL_NAME,r3,COL_NAME,
           val=(f"{stu[0]} {stu[1]}" if stu else ""),
           font=fB, fill=alt_fill, align=aL)
        mg(ws,r1,COL_SEX, r3,COL_SEX,  val=(stu[2] if stu else ""),
           font=fB, fill=alt_fill, align=aC)
        mg(ws,r1,COL_AGE, r3,COL_AGE,  val=(stu[3] if stu else ""),
           font=fB, fill=alt_fill, align=aC)
        mg(ws,r1,COL_SEC, r3,COL_SEC,  val=(stu[4] if stu else ""),
           font=fB, fill=alt_fill, align=aC)
        if is_senior:
            mg(ws,r1,COL_STR,r3,COL_STR, val=(stu[5] if stu else ""),
               font=fBo, fill=alt_fill, align=aC)

        for row, lbl in [(r1,"I"),(r2,"II"),(r3,"AV")]:
            is_av = lbl == "AV"
            sc(ws,row,COL_SEM,lbl,
               font=fSB if is_av else fS,
               fill=fGoldL if is_av else alt_fill,
               align=aC, border=bT)

        # Subject score cells
        for si in range(nsub):
            col = COL_SUBJ0 + si
            clc = cl(col)
            for row in (r1, r2):
                c = ws.cell(row=row, column=col)
                c.font=fS; c.fill=fIn; c.alignment=aC; c.border=bT; c.number_format="0.0"

            c = ws.cell(row=r3, column=col)
            c.value  = (f'=IF(COUNTA({clc}{r1}:{clc}{r2})=0,"",'
                        f'IFERROR(AVERAGE({clc}{r1}:{clc}{r2}),""))')
            c.font=fSB; c.fill=fGoldL; c.alignment=aC; c.border=bT; c.number_format="0.0"

        # Total
        for row in (r1, r2):
            c = ws.cell(row=row, column=COL_TOT)
            c.value=(f'=IF(COUNTA({sc_l}{row}:{ec_l}{row})=0,"",SUM({sc_l}{row}:{ec_l}{row}))')
            c.font=fSB; c.fill=alt_fill; c.alignment=aC; c.border=bT; c.number_format="0.0"

        c = ws.cell(row=r3, column=COL_TOT)
        c.value=(f'=IF(COUNTA({sc_l}{r3}:{ec_l}{r3})=0,"",SUM({sc_l}{r3}:{ec_l}{r3}))')
        c.font=fBo; c.fill=fGoldL; c.alignment=aC; c.border=bT; c.number_format="0.0"

        # Blank avg/rank/result in Sem I/II rows
        for row in (r1, r2):
            for col in (COL_AVG, COL_RANK, COL_RES):
                ws.cell(row=row,column=col).fill   = alt_fill
                ws.cell(row=row,column=col).border = bT

        # Average
        c = ws.cell(row=r3, column=COL_AVG)
        c.value=(f'=IF({tot_l}{r3}="","",IFERROR({tot_l}{r3}/COUNTA({sc_l}{r3}:{ec_l}{r3}),""))')
        c.font=Font(name="Calibri",bold=True,size=10,color="3E2600")
        c.fill=fGold; c.alignment=aC; c.border=bT; c.number_format="0.00"

        # Rank
        c = ws.cell(row=r3, column=COL_RANK)
        c.value=(f'=IF({avg_l}{r3}="","",RANK({avg_l}{r3},'
                 f'{avg_l}${DATA_START+2}:{avg_l}${last_av_row},0))')
        c.font=fBo; c.fill=fGoldL; c.alignment=aC; c.border=bT

        # Result
        c = ws.cell(row=r3, column=COL_RES)
        c.value=(f'=IF({avg_l}{r3}="","",IF({avg_l}{r3}>={pass_mark},'
                 f'"Darbe / Pass","Kufe / Fail"))')
        c.font=fBo; c.fill=fGoldL; c.alignment=aC; c.border=bT

        # Remark
        for row in (r1, r2, r3):
            c = ws.cell(row=row, column=COL_RMK)
            c.fill=fGoldL if row==r3 else alt_fill; c.border=bT; c.alignment=aL

        # Bottom divider per student block
        for col in range(1, LAST_COL+1):
            existing = ws.cell(row=r3,column=col).border
            ws.cell(row=r3,column=col).border = Border(
                left=existing.left, right=existing.right,
                top=existing.top, bottom=Side(style="medium",color=GD))

    # Conditional formatting
    res_range = f"{res_l}{DATA_START+2}:{res_l}{last_av_row}"
    ws.conditional_formatting.add(res_range, FormulaRule(
        formula=[f'ISNUMBER(SEARCH("Pass",{res_l}{DATA_START+2}))'],
        fill=PatternFill(bgColor="C8E6C9"), font=Font(color="1B5E20", bold=True)))
    ws.conditional_formatting.add(res_range, FormulaRule(
        formula=[f'ISNUMBER(SEARCH("Fail",{res_l}{DATA_START+2}))'],
        fill=PatternFill(bgColor="FFCDD2"), font=Font(color="B71C1C", bold=True)))
    ws.conditional_formatting.add(
        f"{avg_l}{DATA_START+2}:{avg_l}{last_av_row}",
        ColorScaleRule(start_type="num",start_value=0,  start_color="FF5252",
                       mid_type="num",  mid_value=50,   mid_color="FFEB3B",
                       end_type="num",  end_value=100,  end_color="4CAF50"))

    # Data validation
    dv_sex = DataValidation(type="list", formula1='"M,F"',
                            allow_blank=True, showErrorMessage=False)
    ws.add_data_validation(dv_sex)
    if is_senior:
        dv_str = DataValidation(type="list", formula1='"NS,SS,GP"',
                                allow_blank=True, showErrorMessage=False)
        ws.add_data_validation(dv_str)
    for n in range(n_rows):
        base = DATA_START + n * 3
        dv_sex.add(ws.cell(row=base, column=COL_SEX))
        if is_senior:
            dv_str.add(ws.cell(row=base, column=COL_STR))

    ws.freeze_panes = ws.cell(row=DATA_START, column=COL_SUBJ0)
    ws.print_title_rows = "1:4"
    print_landscape(ws)
    return ws

# ─────────────────────────────────────────────────────────────
# REPORT CARD  (Kard9 / Kard10 / Kard11 / Kard12)
# ─────────────────────────────────────────────────────────────
def build_kard(wb, grade, stu_list, cfg):
    school_en  = cfg.get('school', SCHOOL_EN)
    school_om  = cfg.get('school_om', SCHOOL_OM)
    principal  = cfg.get('principal', PRINCIPAL)
    ay_ec      = cfg.get('ay_ec', AY_EC)
    ay_gc      = cfg.get('ay_gc', AY_GC)
    pass_mark  = cfg.get('pass_mark', PASS_MARK)

    is_senior = grade in (11, 12)
    subjects  = SUBJ_11_12 if is_senior else SUBJ_9_10

    # Each student gets their own sheet
    for stu_idx, stu in enumerate(stu_list):
        first, last = stu[0], stu[1]
        sex   = stu[2] if len(stu)>2 else ""
        age   = stu[3] if len(stu)>3 else ""
        sec   = stu[4] if len(stu)>4 else "A"
        stream= stu[5] if len(stu)>5 else "GP"
        # Sheet name: Gr9-A-01-Name (truncated to 31 chars Excel limit)
        raw_name = f"Gr{grade}-{sec}-{stu_idx+1:02d}"
        sname = raw_name[:31]
        ws = wb.create_sheet(sname)
        _build_kard_sheet(ws, grade, subjects, school_en, school_om,
                          principal, ay_ec, ay_gc, pass_mark,
                          first, last, sex, age, sec, stream, stu_idx+1)

def _build_kard_sheet(ws, grade, subjects,
                      school_en, school_om, principal,
                      ay_ec, ay_gc, pass_mark,
                      first_name, last_name, sex, age, section, stream, stu_no):
    # Columns: A=Subject | B-G=Sem I (5 parts + total) | H-M=Sem II | N=Remark
    PART_LABELS = ["Qo'ann.\nAssign","Qor.1\nTest 1","Qor.2\nTest 2",
                   "Walak.\nMid","Dhumaa\nFinal","Walig.\nTotal"]
    N_PARTS   = 6
    COL_SUBJ  = 1
    COL_S1_0  = 2;  COL_S1_TOT = 7
    COL_S2_0  = 8;  COL_S2_TOT = 13
    COL_RMK   = 14
    LAST_COL  = 14

    ws.column_dimensions["A"].width = 22
    for c in range(COL_S1_0, LAST_COL+1):
        ws.column_dimensions[cl(c)].width = 8
    ws.column_dimensions[cl(COL_RMK)].width = 14

    # ── Header rows 1-4 ──────────────────────────────────────
    mg(ws,1,1,1,LAST_COL, val=school_en,
       font=Font(name="Calibri",bold=True,size=16,color=WH),
       fill=fHdr, align=aC)
    ws.row_dimensions[1].height = 36

    mg(ws,2,1,2,LAST_COL, val=school_om,
       font=Font(name="Calibri",bold=True,size=10,color=WH),
       fill=fSub, align=aC)
    ws.row_dimensions[2].height = 20

    mg(ws,3,1,3,LAST_COL,
       val=f"KAARDII GABASSAA BARATAA  /  STUDENT REPORT CARD  ·  Kutaa/Grade {grade}",
       font=Font(name="Calibri",bold=True,size=11,color="3E2600"),
       fill=fGold, align=aC)
    ws.row_dimensions[3].height = 22

    mg(ws,4,1,4,LAST_COL,
       val=f"{WOREDA}, {ZONE}, {REGION}, Ethiopia  ·  {ay_ec} EC / {ay_gc} GC",
       font=Font(name="Calibri",size=9,color=WH),
       fill=fSub, align=aC)
    ws.row_dimensions[4].height = 16

    # ── Student info rows 5-10 ───────────────────────────────
    info_fields = [
        (f"Maqaa Barataa / Student Name", f"{first_name} {last_name}",
         "Kutaa / Grade & Section",        f"{grade}-{section}"),
        ("Lak. ID / Student ID",           f"G{grade:02d}-{stu_no:04d}",
         "Saala / Gender",                 sex),
        ("Bara Dhalootaa / Date of Birth", "",
         "Umurii / Age",                   age),
        ("Maqaa Abbaa / Father's Name",    "",
         "Bilbila / Phone",                ""),
        ("Maqaa Haadhaa / Mother's Name",  "",
         "Aanaa / District",               WOREDA),
        ("Ganda / Kebele",                 "",
         "Guyyaa Gabassaa / Report Date",  ""),
    ]
    for i,(ll,lv,rl,rv) in enumerate(info_fields):
        row = 5 + i
        ws.row_dimensions[row].height = 17
        mg(ws,row,1,row,3, val=ll,
           font=Font(name="Calibri",bold=True,size=9,color=WH),
           fill=fSub, align=aL)
        mg(ws,row,4,row,7, val=lv, font=fB, fill=fIn, align=aL)
        mg(ws,row,8,row,10, val=rl,
           font=Font(name="Calibri",bold=True,size=9,color=WH),
           fill=fSub, align=aL)
        mg(ws,row,11,row,LAST_COL, val=rv, font=fB, fill=fIn, align=aL)

    ws.row_dimensions[11].height = 6

    # ── Academic performance table header (rows 12-14) ───────
    mg(ws,12,1,12,LAST_COL,
       val="ADEEMSA BARNOOTAA / ACADEMIC PERFORMANCE",
       font=Font(name="Calibri",bold=True,size=11,color=WH),
       fill=fHdr, align=aC)
    ws.row_dimensions[12].height = 22

    mg(ws,13,COL_SUBJ,13,COL_SUBJ, val="Barnoota\nSubject", font=fH, fill=fHdr, align=aC)
    mg(ws,13,COL_S1_0,13,COL_S1_TOT, val="TERM I / SEMESTER I",
       font=Font(name="Calibri",bold=True,size=10,color=WH), fill=fSub, align=aC)
    mg(ws,13,COL_S2_0,13,COL_S2_TOT, val="TERM II / SEMESTER II",
       font=Font(name="Calibri",bold=True,size=10,color=WH), fill=fSub, align=aC)
    mg(ws,13,COL_RMK,13,COL_RMK, val="Yaada\nRemark", font=fH, fill=fHdr, align=aC)
    ws.row_dimensions[13].height = 22

    sc(ws,14,COL_SUBJ,"",font=fH,fill=fHdr,align=aC,border=bT)
    for i,lbl in enumerate(PART_LABELS):
        for sem_off in (0, N_PARTS):
            col = COL_S1_0 + sem_off + i
            c = ws.cell(row=14, column=col)
            c.value=lbl; c.font=Font(name="Calibri",bold=True,size=8,color=WH)
            c.fill=fHdr; c.alignment=aRot; c.border=bT
    sc(ws,14,COL_RMK,"",font=fH,fill=fHdr,align=aC,border=bT)
    ws.row_dimensions[14].height = 58

    # ── Subject rows (15+) ────────────────────────────────────
    DATA_SUBJ_START = 15
    for si,(om,en) in enumerate(subjects):
        row = DATA_SUBJ_START + si
        ws.row_dimensions[row].height = 17
        rf  = fAlt if si%2==1 else fWht
        c = ws.cell(row=row, column=COL_SUBJ)
        c.value=f"{om}  /  {en}"; c.font=Font(name="Calibri",bold=True,size=9)
        c.fill=rf; c.alignment=aL; c.border=bT
        for sem in (0,1):
            s0 = COL_S1_0 + sem * N_PARTS
            for p in range(5):
                c = ws.cell(row=row,column=s0+p)
                c.fill=fIn; c.alignment=aC; c.border=bT; c.number_format="0"
            p1=cl(s0); p5=cl(s0+4)
            c = ws.cell(row=row,column=s0+5)
            c.value=(f'=IF(COUNTA({p1}{row}:{p5}{row})=0,"",'
                     f'SUM({p1}{row}:{p5}{row}))')
            c.font=fSB; c.fill=fGoldL; c.alignment=aC; c.border=bT; c.number_format="0.0"
        c=ws.cell(row=row,column=COL_RMK)
        c.fill=rf; c.alignment=aL; c.border=bT

    n_subjs    = len(subjects)
    AFTER_ROW  = DATA_SUBJ_START + n_subjs
    ws.row_dimensions[AFTER_ROW].height = 6
    SR = AFTER_ROW + 1

    # ── Summary / Attendance / Behaviour header ───────────────
    mg(ws,SR,1,SR,LAST_COL,
       val="WALIGALA / ARGAMA / BEHAVIOUR  ·  SUMMARY / ATTENDANCE / LIFE SKILLS",
       font=Font(name="Calibri",bold=True,size=10,color=WH),
       fill=fHdr, align=aC)
    ws.row_dimensions[SR].height = 20; SR += 1

    P1_S=1;P1_E=4; P2_S=5;P2_E=9; P3_S=10;P3_E=LAST_COL
    for cs,ce,lbl in [(P1_S,P1_E,"WALIGALA / SUMMARY"),
                      (P2_S,P2_E,"ARGAMA / ATTENDANCE"),
                      (P3_S,P3_E,"BEHAVIOUR & LIFE SKILLS")]:
        mg(ws,SR,cs,SR,ce,val=lbl,
           font=Font(name="Calibri",bold=True,size=9,color=WH),
           fill=fSub,align=aC)
    ws.row_dimensions[SR].height = 18; SR += 1

    sum_items = ["Waligala Sem I / Total Term I",
                 "Waligala Sem II / Total Term II",
                 "Gidgala / Overall Average",
                 "Sadarkaa / Class Rank",
                 "Baay. Kutaa / Class Size",
                 "Bu'aa / Result"]
    att_items = ["Guyyaa Barumsaa / School Days",
                 "Argamuu / Present",
                 "Hin Argamne / Absent",
                 "Dheera / Late",
                 "% Argama / Attendance %"]
    beh_items = ["Naamusa / Discipline","Kabajaa / Respect",
                 "Hirmaannaa / Participation","Hogganummaa / Leadership",
                 "Gamtaan hoj. / Teamwork","Itti gaaf. / Responsibility"]
    RATINGS   = ["Excellent","Very Good","Good","Fair","Needs Improve"]

    # Rating column headers in panel 3 row SR
    for ri,rat in enumerate(RATINGS):
        c = ws.cell(row=SR-1+1, column=P3_S+1+ri)
    # Write rating labels above beh rows (already on SR which is sub-header)
    for ri,rat in enumerate(RATINGS):
        c = ws.cell(row=SR, column=P3_S+1+ri)
        c.value=rat; c.font=Font(name="Calibri",bold=True,size=7,color=WH)
        c.fill=fSub; c.alignment=Alignment(horizontal="center",vertical="center",
                                           wrap_text=True,text_rotation=90); c.border=bT

    for row_off in range(max(len(sum_items),len(att_items),len(beh_items))):
        row = SR + row_off
        ws.row_dimensions[row].height = 17

        if row_off < len(sum_items):
            mg(ws,row,P1_S,row,P1_S+1,val=sum_items[row_off],
               font=Font(name="Calibri",bold=True,size=9),fill=fGoldL,align=aL)
            mg(ws,row,P1_S+2,row,P1_E,font=fB,fill=fIn,align=aC)

        if row_off < len(att_items):
            mg(ws,row,P2_S,row,P2_S+2,val=att_items[row_off],
               font=Font(name="Calibri",bold=True,size=9),fill=fGoldL,align=aL)
            mg(ws,row,P2_S+3,row,P2_E,font=fB,fill=fIn,align=aC)

        if row_off < len(beh_items):
            c=ws.cell(row=row,column=P3_S)
            c.value=beh_items[row_off]; c.font=Font(name="Calibri",bold=True,size=9)
            c.fill=fGoldL; c.alignment=aL; c.border=bT
            for ri in range(len(RATINGS)):
                c=ws.cell(row=row,column=P3_S+1+ri)
                c.value="[  ]"; c.font=Font(name="Calibri",size=9)
                c.fill=fWht; c.alignment=aC; c.border=bT

    SR += max(len(sum_items),len(att_items),len(beh_items))

    # Promotion status
    ws.row_dimensions[SR].height = 20
    mg(ws,SR,1,SR,4,val="Guddina / Promotion Status:",
       font=Font(name="Calibri",bold=True,size=10),fill=fGoldL,align=aL)
    for txt,col in [("[ ] Darbe/Promoted",5),("[ ] Kufe/Detained",7),("[ ] Hin Darb./Not Promoted",10)]:
        c=ws.cell(row=SR,column=col)
        c.value=txt; c.font=fBo; c.fill=fWht; c.alignment=aL; c.border=bT
    SR += 1

    # Comments
    ws.row_dimensions[SR].height = 8; SR += 1
    for lbl,cs,ce in [("Yaada Barsiisaa Daree / Homeroom Teacher Comment",1,7),
                       ("Yaada Maatii / Parent / Guardian Comment",8,LAST_COL)]:
        mg(ws,SR,cs,SR,ce,val=lbl,
           font=Font(name="Calibri",bold=True,size=9,color=WH),fill=fSub,align=aL)
    ws.row_dimensions[SR].height = 18; SR += 1
    for _ in range(3):
        ws.row_dimensions[SR].height = 18
        for cs,ce in [(1,7),(8,LAST_COL)]:
            mg(ws,SR,cs,SR,ce,
               val="....................................................................",
               font=Font(name="Calibri",size=9,color="AAAAAA"),fill=fWht,align=aL)
        SR += 1

    # Signatures
    ws.row_dimensions[SR].height = 8; SR += 1
    mg(ws,SR,1,SR,LAST_COL,val="SAHIHHOO / SIGNATURES",
       font=Font(name="Calibri",bold=True,size=10,color=WH),fill=fHdr,align=aC)
    ws.row_dimensions[SR].height = 18; SR += 1

    sigs=[("Barsiisaa Daree\nHomeroom Teacher",1,4),
          ("Maatii / Guarantor\nParent / Guardian",5,9),
          (f"{principal}\nSchool Director",10,LAST_COL)]
    ws.row_dimensions[SR].height = 34
    for nm,cs,ce in sigs:
        mg(ws,SR,cs,SR,ce,val=nm,
           font=Font(name="Calibri",bold=True,size=9),fill=fGoldL,align=aC)
    SR += 1
    ws.row_dimensions[SR].height = 14
    for _,cs,ce in sigs:
        mg(ws,SR,cs,SR,ce,val="________________________________",
           font=Font(name="Calibri",size=9,color="777777"),fill=fWht,align=aC)
    SR += 1
    ws.row_dimensions[SR].height = 14
    for _,cs,ce in sigs:
        mg(ws,SR,cs,SR,ce,val="Guyyaa / Date: _______________",
           font=Font(name="Calibri",size=9),fill=fWht,align=aC)
    SR += 1

    ws.row_dimensions[SR].height = 8; SR += 1
    mg(ws,SR,1,SR,LAST_COL,
       val=f"  {WOREDA}  •  {ZONE}  •  {REGION}  •  Ethiopia  ",
       font=Font(name="Calibri",size=9,color=WH),fill=fSub,align=aC)
    ws.row_dimensions[SR].height = 16

    print_portrait(ws)
    ws.print_area = f"A1:{cl(LAST_COL)}{SR}"

# ─────────────────────────────────────────────────────────────
# SUPPORT SHEETS
# ─────────────────────────────────────────────────────────────
def build_inf0(wb, cfg):
    ws = wb.create_sheet("Inf0")
    mg(ws,1,1,1,4,
       val="Inf0 · School Information",
       font=fT, fill=fHdr, align=aC)
    ws.row_dimensions[1].height = 28
    fields = [
        ("School Name (English)",    cfg.get('school', SCHOOL_EN)),
        ("School Name (Afan Oromo)", cfg.get('school_om', SCHOOL_OM)),
        ("Region",  REGION), ("Zone", ZONE), ("Woreda", WOREDA),
        ("Principal",     cfg.get('principal', PRINCIPAL)),
        ("Academic Year (EC)", cfg.get('ay_ec', AY_EC)),
        ("Academic Year (GC)", cfg.get('ay_gc', AY_GC)),
        ("Pass Mark (%)",      cfg.get('pass_mark', PASS_MARK)),
        ("Grades",  "9 – 12"),
        ("Sections","A – K"),
    ]
    for i,(lbl,val) in enumerate(fields):
        row = i + 2
        ws.row_dimensions[row].height = 18
        mg(ws,row,1,row,2,val=lbl,
           font=fBo,fill=fSub if i%2==0 else fGray,align=aL)
        mg(ws,row,3,row,4,val=val,
           font=fB,fill=fIn,align=aL)
    for col,w in [(1,28),(2,28),(3,28),(4,28)]:
        ws.column_dimensions[cl(col)].width = w

def build_baafata(wb, grades, has_kard):
    ws = wb.create_sheet("Baafata", 0)
    mg(ws,1,1,1,4,
       val="BAAFATA  ·  TABLE OF CONTENTS",
       font=fT, fill=fHdr, align=aC)
    ws.row_dimensions[1].height = 32
    row = 3
    for group, entries in [
        ("SETUP",  [("Inf0","School info, year, pass mark")]),
        ("ROSTERS",[("Ros9","Grade 9 Roster (12 subjects)"),
                    ("Ros10","Grade 10 Roster (12 subjects)"),
                    ("Ros11","Grade 11 Roster (NS/SS, 10 subjects)"),
                    ("Ros12","Grade 12 Roster (NS/SS, 10 subjects)")] if grades==[9,10,11,12] else
                   [(f"Ros{g}","") for g in grades]),
    ]:
        mg(ws,row,1,row,4,val=group,
           font=Font(name="Calibri",bold=True,size=11,color=WH),
           fill=fGold if group=="ROSTERS" else fSub, align=aL)
        ws.row_dimensions[row].height = 22; row += 1
        for sname,desc in entries:
            if sname not in wb.sheetnames: continue
            ws.row_dimensions[row].height = 18
            c = ws.cell(row=row,column=1)
            c.value=sname; c.font=fLk
            c.fill=fAlt if row%2==0 else fWht; c.alignment=aL; c.border=bT
            try: c.hyperlink=f"#{sname}!A1"
            except: pass
            d = ws.cell(row=row,column=2)
            d.value=desc; d.font=fB
            d.fill=fAlt if row%2==0 else fWht; d.alignment=aL; d.border=bT
            row += 1
        row += 1
    ws.column_dimensions["A"].width = 18
    ws.column_dimensions["B"].width = 50

# ─────────────────────────────────────────────────────────────
# MAIN
# ─────────────────────────────────────────────────────────────
def main():
    parser = argparse.ArgumentParser(description="SJASS Excel Roster + Report Card Generator")
    parser.add_argument('--data',   help='JSON file path with student data from PHP')
    parser.add_argument('--output', default='SJASS_Records.xlsx', help='Output .xlsx path')
    parser.add_argument('--grade',  default='all', help='Grade: 9|10|11|12|all')
    parser.add_argument('--kard',   action='store_true', help='Generate per-student Kard sheets')
    args = parser.parse_args()

    # Load config + student data
    cfg      = {}
    students = {}
    if args.data and os.path.exists(args.data):
        with open(args.data, encoding='utf-8') as f:
            payload = json.load(f)
        cfg      = payload.get('config', payload)  # support both shapes
        students = {int(k): v for k,v in payload.get('students',{}).items()}

    # Fall back to sample data for any missing grade
    for g in (9,10,11,12):
        if g not in students:
            students[g] = SAMPLE_STUDENTS[g]

    # Determine which grades to generate
    if args.grade == 'all':
        grades = [9, 10, 11, 12]
    else:
        grades = [int(x) for x in args.grade.split(',')]

    wb = openpyxl.Workbook()
    wb.remove(wb.active)

    build_inf0(wb, cfg)

    for g in grades:
        stu_list = students.get(g, [])
        build_roster(wb, g, stu_list, cfg)

    if args.kard:
        for g in grades:
            stu_list = students.get(g, [])
            if stu_list:
                build_kard(wb, g, stu_list, cfg)

    build_baafata(wb, grades, args.kard)

    # Tab colours
    colours = {"Baafata":"1A5E20","Inf0":"2E7D32",
               "Ros9":"F57F17","Ros10":"F9A825","Ros11":"FF8F00","Ros12":"FF6F00"}
    for sname, col in colours.items():
        if sname in wb.sheetnames:
            wb[sname].sheet_properties.tabColor = col
    for sname in wb.sheetnames:
        if sname.startswith("Gr"):
            wb[sname].sheet_properties.tabColor = "1565C0"

    wb.save(args.output)
    print(f"[OK] {args.output}", flush=True)

if __name__ == "__main__":
    main()
