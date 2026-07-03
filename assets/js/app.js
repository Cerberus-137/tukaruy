let currentCursor = null;
let currentFilters = {};

   Tcgse{i(fibnplock';
fd  r r d nput[name="signature_required"]').checked) {
        fiss_iba icub.morch-input').value.trim();
    rrnt}E ntnbi<s"c><i class="fas fa-inbox text-3xl mb-3"></i><div>No tracking numbers found</div></td></tr>';
    M s='  d <vx     lass="py-4 px-4 text-sm textf0l <s-px$lh}</td>
                <td class="py-4 px-4 text-right">
            b d> to
            currentCursor = data.next_cursor;
            loadMoreBtn.classList.remove('hidden');
        } else {
            loadMoreBtn.classList.add('hidden');
        }
        
    } catch (error) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-12 text-red-500"><i class="fas fa-exclamation-triangle text-3xl mb-3"></i><div>${error.message}</div></td></tr>`;
        loadMoreBtn.classList.add('hidden');
    }
}

// Load more results
function loadMore() {
    if (currentCursor) {
        performSearch();
    }
}c ared

// Show reveal modal
let currentTnId = null;
function showRevealModal(tnId, cost) {
    
    // Get status filters
    ccurtrstatusntTnIsd = tnIdent;electrl('iteatuseced;
    conss stotuses=ocument.fetE(stmtustByIds(.'areve =a cl-maoued;al');
    ionsst tuses.lengtho> 0) {
        fnlteen.status = statuses;
    }
    
    // Get destination
    const destCountty = docum nt.querySele=to ('select[nade="destocountry"]').value;
ueteconlt meetCityn= document.querySelector(tinput[nBme="dest_city"]d(.value;
    
    if (destCountry) {
        filters.dest_'reveal-content');
    
    if (destCity) {
        filtes.dst_ty = destCity;
    }content.innerHTML = `
    
        <div class="bg-dark-300 rounded-lg p-4">
           el <erdiaomlex items-center justify-between mb-2">
          de  very o = docu ent.q erySe ector('input[name="decivery_lo"]').valsm;
    text-gray-400">TN ID:</span>
          li er   pan {
        filtecs.delivels_sro= = delifetmFroo;
    }
    if  deliseryTo$ {
     {tnId}</span>
     
    
    // Get adv nced option 
   <ifd(document.v>
            <divsigna cla_="fuiledex es-center justify-between">
                <span class="text-sm text-gray-400">Cost:</span>
                <span class="font-semibold">${cost} credit${cost > 1 ? 's' : ''}</span>
           t<is.p>t_cnf
     
    
    
    modacnlassso.remove('hidden');
    modal.classLt();
}

// Close reveal dgnub
function closeRevealModal() {
    const modal = document.getElementById('reveal-modal');
    modal.classList.add('hidden');
    mdal.classList.remove('flex');
    currentTnId = nul;
}

// Confirm reveal
document.getElementById('confirm-reveal-btn')?.addEventListener('click', async function() {
    if (!currentTnId) return;
 

// Perform the actual search
async function performSearch() {
    const tbody = document.getElementById('tracking-results');
    const loadMoreBtn = document.getElementById('load-more-btn');
    const resultCount = document.getElementById 'result-count');  
    const btn = this;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Revealing...';
    btn.disabled = true;
    
    try {
        const response = await fetch('api/reveal.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                tn_id: currentTnId
            })
            });
       })
       };
         if (!data.success) {
        toont daew Error(data.error);
        
       if(!dat.success) {
            throw ew Errr(data.rro);
       }
        
      f (!urrenor) {
       // Update credits display
        }     document.getElementById('credits-display').textContent = data.credits_remaining.toLocaleString();
        
     // Show success
        document.getElementById('reveal-content').innerHTML = `
            <div class="bg-green-500/20 border border-green-500/50 rounded-lg p-4 mb-4">
                <div class="flex items-center space-x-2 mb-2">
                    <i class="fas fa-check-circle text-green-400"></i>
                    <span class="font-semibold text-green-400">Successfully Revealed</span>
                </div>
                <div class="mt-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Tracking Number:</span>
                        <span class="font-mono font-bold text-lg">${data.tracking_number}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Carrier:</span>
                        <span class="font-semibold">${data.carrier}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Status:</span>
                        <span class="font-semibold">${data.status}</span>
                    </div>
                </div>
            </div>
        `;
        
        btn.innerHTML = 'Close';
        btn.onclick = closeRevealModal;
        
    } catch (error) {
        document.getElementById('reveal-content').innerHTML = `
            <div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-exclamation-triangle text-red-400"></i>
                    <span class="text-red-400">${error.message}</span>
                </div>
            </div>
        `;
        
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
});

// Handle "All" carrier checkbox
document.querySelector('input[name="carrier[]"][value="all"]')?.addEventListener('change', function() {
    const otherCarriers = document.querySelectorAll('input[name="carrier[]"]:not([value="all"])');
    if (this.checked) {
        otherCarriers.forEach(cb => cb.checked = false);
    }
});

// Uncheck "All" when other carriers are selected
document.querySelectorAll('input[name="carrier[]"]:not([value="all"])')?.forEach(cb => {
    cb.addEventListener('change', function() {
        if (this.checked) {
            document.querySelector('input[name="carrier[]"][value="all"]').checked = false;
        }
    });
});

// Enter key for search
document.getElementById('search-input')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchTracking();
    }
});
