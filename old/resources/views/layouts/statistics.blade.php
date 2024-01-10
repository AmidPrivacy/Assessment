
<table id="datatable">
	<tr>
		<td width="100%"> 
			<div class="btn-group" role="group" aria-label="Basic example" style="width: 99%; margin-left: 0.7%; margin-bottom: 15px;margin-top: 10px">
				<div class="col-md-6">
					<button 
						type="button" 
						class="btn btn-secondary" 
						style="background-color: #0867c5; 
									color: #fff;
									font-weight: 700;
									width: 100%;
									border: none;
									margin-left: -1%"
					>
						Dinlənilən zəng sayı: {{ $data['assessmentCalls'] }}
					</button> 
				</div>
				<div class="col-md-6">
					<button type="button" class="btn btn-secondary"
						style="background-color: #0867c5; color: #ffffff; font-weight: 700; border: none; width: 100%">
						Zənglər arası verilən güzəşt: {{ $data['additional'] }} (saniyə)
					</button>
				</div> 
			</div>
			<div class="statistic">
				<div class="title">Sistemdə keçirdiyi vaxt</div>
				<div class="numbers"> {{ $data['loginTime'] }} </div>
			</div> 
			<div class="statistic">
				<div class="title">İş saatı</div>
				<div class="numbers"> {{ $data['workTime'] }} </div>
			</div> 
			<div class="statistic">
				<div class="title">Zəngdə keçirdiyi vaxt</div>
				<div class="numbers"> {{ $data['callSpendTimes'] }} </div>
			</div> 
			<div class="statistic">
				<div class="title">Play vaxtı</div>
				<div class="numbers"> {{ $data['playTime'] }} </div>
			</div>  
			<div class="statistic">
				<div class="title" style="background-color: brown">İşdən yayınma - sistem üzrə</div>
				<div class="numbers"> {{ $data['freeTimeAvoidance'] }} </div>
			</div>  
			<div class="statistic">
				<div class="title" style="background-color: brown">İşdən yayınma - zəng arası</div>
				<div class="numbers"> {{ $data['callAvoidance'] }} </div>
			</div>  
			{{-- <div class="statistic">
				<div class="title">Dinləmə vaxt</div>
				<div class="numbers"> {{ $data['workTime'] }} </div>
			</div>  --}}
		</td>
	</tr>
	<tr>  
		<textarea id="user-comment" rows="5" placeholder="Qeyd daxil edin">{{ $data['comment'] }}</textarea>
		<button 
			type="submit"
			id="user-comment-send" 
			>Göndər</button>
	</tr>
	@if(Auth::user()->role == 2)
	<tr> 
		<div class="input-group mb-3" style="width: 96%; margin-left: 2%; margin-top: 11px"> 
				<input type="text" class="form-control" 
					placeholder="Prioritet daxil edin" 
					aria-describedby="button-addon2" 
					id="additionalTime" value="{{ $data['additional'] }}">
				<div class="input-group-append">
					<button class="btn btn-outline-secondary" type="submit" id="refreshTime">Yenilə</button>
				</div> 
		</div>
	</tr>
	@endif
</table>