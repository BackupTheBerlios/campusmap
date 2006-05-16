package CampusMap;

import javax.swing.*;
import java.awt.*;
import java.awt.Color;

import javax.swing.border.*;
import javax.swing.BorderFactory; 

import java.io.IOException;
import java.net.*;

/**
 * @author kriegerischerKämpfer
 *
 * TODO To change the template for this generated type comment go to
 * Window - Preferences - Java - Code Style - Code Templates
 */
public class Environment extends JApplet{

	public static boolean adminMode = false;
	
	public static String address;
	public static String cpmFolder = "/cpm/";
	
	public static String phpRoot;

	public static final String guiFile = "guiFile.xml";
	public static final String geoFile = "geoFile.xml";

	public static final String modelFolder = "models/";
	public static final String ressourceFolder = "res/";

	public static final String textFileFolder = "text/";
	//public static final String pathFolder = "text/pathes/";

	final static Color fh_CI_Color = new Color(221, 16, 47);
	final static Color bg_Color = new Color(240, 240, 255);
	
	
	// Contents
	// Beginning Labels
	JLabel initDisplay;
	JLabel objectInitDisplay;
	
	// Project contents
	CampusMap theContent;
	static Explications infoBox;
	private JLayeredPane layeredPane;
	private boolean loadingLabelShowing=false;
    JTextArea loadingLabel;

	// Content Holder
	static JPanel contentHolder;

	// Containertree
	CpmPanel toolTipPanel;
	static CpmTextArea toolTipTextfield;

	JScrollPane infoBoxScrollPane;
	
	//Layout
	GridBagLayout gridbag;
	GridBagConstraints c;
		
	Font centuryFont;
	Font centuryFontSmall;
	static Border redline;
	static Border grayline;
	
	Dimension area;
	
	public void init()
	{
		if (getParameter("cpmFolder")!=null)
			cpmFolder =  "/" + getParameter("cpmFolder");
		address = getDocumentBase().toString();
		if (address.startsWith("file:/")) address = "http://localhost" + cpmFolder;
		else address = "http://"+getDocumentBase().getHost().toString() + cpmFolder;
		System.out.println(address);
		
		if (getParameter("phpRoot")!=null)
			phpRoot =  getParameter("phpRoot");
		else
			phpRoot = address + textFileFolder;
		
		if (getParameter("admin")!=null) {
			adminMode =  Boolean.valueOf(getParameter("admin")).booleanValue();
		}
		
		this.getContentPane().setLayout(new BoxLayout(this.getContentPane(), BoxLayout.Y_AXIS));
		centuryFont = new Font("Century Gothic", Font.PLAIN, 14);
		centuryFontSmall = new Font("Century Gothic", Font.PLAIN, 12);
		// 
		initDisplay = new JLabel("Initialising: ");
		initDisplay.setFont(centuryFont);
		this.getContentPane().add(initDisplay);
		objectInitDisplay = new JLabel("Environment");
		objectInitDisplay.setFont(centuryFont);
		this.getContentPane().add(objectInitDisplay);

		
		//Content
		theContent = new CampusMap();
		redline = BorderFactory.createLineBorder(fh_CI_Color);
		grayline = BorderFactory.createLineBorder(Color.DARK_GRAY);
		
		// Tooltips
		toolTipTextfield = new CpmTextArea("Bitte noch ein bisschen warten. Die Geometriedaten werden geladen...", this.contentHolder);
		toolTipTextfield.setForeground(Color.GRAY);
		toolTipTextfield.setFont(centuryFontSmall);
		toolTipTextfield.setBorder(null);
		toolTipTextfield.setOpaque(false);
		toolTipTextfield.setLineWrap(true);
		toolTipTextfield.setWrapStyleWord(true);
		toolTipTextfield.setEditable(false);
		toolTipTextfield.setMinimumSize(new Dimension(250, 30));

		toolTipPanel = new CpmPanel((JApplet)this, "logotype.png");
		toolTipPanel.setBorder(grayline);
		toolTipPanel.add(toolTipTextfield);
		
//		detailButton = new JButton();
//		URL iconUrl=null;
//		try{
//			iconUrl = new URL(Environment.address+Environment.ressourceFolder+"detailModusButton.gif");
//			detailButton.setIcon(new ImageIcon(iconUrl));
//			detailButton.setContentAreaFilled(false);
//		}catch(MalformedURLException e){
//			e.printStackTrace();
//		}
//		
					
//		toolTipPanel.add(detailButton);
		
		SpringLayout layout = new SpringLayout();
		toolTipPanel.setLayout(layout);    //Adjust constraints for the label so it's at (5,5).
		
		layout.putConstraint(SpringLayout.WEST, toolTipTextfield,35,SpringLayout.WEST, toolTipPanel);
		layout.putConstraint(SpringLayout.NORTH, toolTipTextfield,0,SpringLayout.NORTH, toolTipPanel);
//		layout.putConstraint(SpringLayout.WEST, detailButton,5,SpringLayout.EAST, toolTipTextfield);
//		layout.putConstraint(SpringLayout.NORTH, toolTipTextfield,5,SpringLayout.NORTH, toolTipPanel);
	    layout.putConstraint(SpringLayout.EAST, toolTipPanel,270,SpringLayout.EAST, toolTipTextfield);
	    layout.putConstraint(SpringLayout.SOUTH, toolTipPanel,5,SpringLayout.SOUTH, toolTipTextfield);

		infoBox = new Explications(this);
		infoBox.setBackground(bg_Color);

		
		infoBoxScrollPane = new JScrollPane();
		infoBoxScrollPane.setBorder(grayline);
		infoBoxScrollPane.setViewportView(infoBox);
		area = new Dimension(570, 250);
		infoBoxScrollPane.setMinimumSize(area);
		
		layeredPane = new JLayeredPane();
		infoBoxScrollPane.setSize(570, 250);
		layeredPane.add(infoBoxScrollPane, JLayeredPane.DEFAULT_LAYER);
		layeredPane.setPreferredSize(new Dimension(400,400));
		
        loadingLabel = new JTextArea();
        loadingLabel.setAlignmentY(Component.CENTER_ALIGNMENT);
        loadingLabel.setAlignmentX(Component.CENTER_ALIGNMENT);
        loadingLabel.setOpaque(true);
        loadingLabel.setBackground(Environment.bg_Color);
        loadingLabel.setForeground(Color.black);
        loadingLabel.setBorder(BorderFactory.createLineBorder(Color.black));
        loadingLabel.setBounds(0, 0, 570, 250);

		
		contentHolder = new JPanel();
		contentHolder.setLayout(new BorderLayout());
		contentHolder.add(theContent, BorderLayout.CENTER);
		contentHolder.setBorder(grayline);

		// starting
		this.getContentPane().setBackground(Color.white);
		((JComponent)this.getContentPane()).setBorder(redline);
		this.getContentPane().add(toolTipPanel, BorderLayout.CENTER);
		theContent.setEnvironment(this);
		
		theContent.init();
	}	
	
	public void addThem(){
		System.out.println("addThem");
		c = new GridBagConstraints();
		gridbag = new GridBagLayout();

		this.setContentPane(new JPanel());
		this.getContentPane().setBackground(Color.white);
		((JComponent)this.getContentPane()).setBorder(redline);
		this.getContentPane().setLayout(gridbag);
		
		c.gridx = 0;
		c.gridy = 0;
		c.weightx = 0.0;
		c.ipady = 340;
		c.ipadx = 560;
		c.insets = new Insets(5,5,5,5);		
		c.fill = GridBagConstraints.NONE;
		gridbag.setConstraints(contentHolder, c);
		this.getContentPane().add(contentHolder);
		
		c.gridx = 0;
		c.gridy = 1;
		c.ipady = 0;       //reset to default
		c.ipadx = 0;
		c.weightx = 0.0;
		c.insets = new Insets(5,5,5,5);		
		c.fill = GridBagConstraints.BOTH;
		gridbag.setConstraints(toolTipPanel, c);
		this.getContentPane().add(toolTipPanel);
		
		c.weighty = 1.0;   //request any extra vertical space
		c.gridx = 0;       //aligned left
		c.gridy = 2;       //third row
		c.fill = GridBagConstraints.BOTH;
		gridbag.setConstraints(layeredPane, c);
		this.getContentPane().add(layeredPane);
		
		toolTipTextfield.setText("Geometrie wird weiter geladen. Bitte entschuldigen sie evtl. Verzögerungen.");
		toolTipPanel.setBorder(grayline);
	}
	/**
	 * Sets an error Text in teh tooltip-window which blinks by default 99 times.
	 * 
	 * @param text sets the Text
	 */
	public void setErrorDisplay(String text){
		toolTipTextfield.setText(text, 99);
	}
	
	public void showLoadingLayer(String text){
		System.out.println("loading Layer shown");
		if(!loadingLabelShowing){
			loadingLabel.setText(text+"\n");
			layeredPane.add(loadingLabel, JLayeredPane.MODAL_LAYER);
			loadingLabelShowing=true;
		}else loadingLabel.setText(text+"\n");
	}
	
	public void increaseLoadingBar(){
		loadingLabel.setText(loadingLabel.getText()+".");
		loadingLabel.validate();
	}
	
	public void hideLoadingLayer(){
		layeredPane.remove(loadingLabel);
		loadingLabelShowing=false;
		System.out.println("loading Layer hidden");
//		layeredPane.validate();
	}
	
	/**
	 * Tooltip display
	 * 
	 * @param text
	 * @param blinkAmount Sets the amount to blink for the input text
	 */
	public static void setToolTip(String text, int blinkAmount){
		if(blinkAmount!=0)toolTipTextfield.setText(text, blinkAmount);
		else toolTipTextfield.setText(text);
	} 
	
	/**
	 * deleting the tooltipdisplay
	 *
	 */
	public static void clearToolTip(){
		toolTipTextfield.setText("");
	}
	
	public void repaintEnv(){
		//this.validate();
		layeredPane.revalidate();
	}
	

	public void dispose(){
		theContent.destroy();
	}
	
	public void paintComponent(Graphics g){
		setForeground(bg_Color);
		g.drawRect(0,0,getWidth(),getHeight());
	}
	
	public static void setBrowserUrl(String url) {
		try {
			infoBox.textDisplay.setPage(url);
		} catch (IOException e) {}
	}
	
}


class CpmPanel extends JPanel{
	
	Image bgImg1;
	Image bgImg2;
	
	public CpmPanel(){/**/}
	
	public CpmPanel(JApplet loadApplet, String imgName){
		try{
			bgImg1=loadApplet.getImage(new URL(Environment.address+Environment.ressourceFolder), imgName);
			bgImg2=loadApplet.getImage(new URL(Environment.address+Environment.ressourceFolder), "Stewardess.gif");
		}catch(MalformedURLException e){
			System.out.println("Error loading backgroundimage");
		}
	}
	
	public void paintComponent(Graphics g){
		//setForeground(Environment.fh_CI_Color);
		setForeground(Environment.bg_Color);
		g.fillRect(0,0, this.getWidth(), this.getHeight());
		//g.fillRect(0,0, 15, this.getHeight());
		if(bgImg1!=null)g.drawImage(bgImg1, this.getWidth()-270, 5, this);
		if(bgImg2!=null)g.drawImage(bgImg2, -10, this.getHeight()-40, this);
	}
}

class CpmTextArea extends JTextArea implements Runnable{
    int frame=0;
    int delay=1000;
    int amount=4;
    int counter;
    Thread animator;
    JPanel myRedLine;
 
    public CpmTextArea(String p_text, JPanel toAnimateToo){
    	super(p_text);
		myRedLine=toAnimateToo;
    }
    
	private void animate(int p_blinkAmount){
		amount=p_blinkAmount;
		counter=0;
		if(p_blinkAmount!=0){
			animator = new Thread(this);
			animator.start();
		}
	}
	
	public void setText(String text){
		System.out.println(text);
		super.setText(text);
		stop();
	}

	public void setText(String text, int animCount){
		super.setText(text);
		animate(animCount);
	}
	
    /**
     * This method is called by the thread that was created in
     * the start method. It does the main animation.
     */
    public void run() {
		while (Thread.currentThread() == animator) {
		    if(counter<amount){
			    // Advance the frame
			    if(frame==0){
			    	frame=1;
			    	setForeground(Environment.fh_CI_Color);
//			    	if(myRedLine!=null)myRedLine.setBorder(new LineBorder(Environment.fh_CI_Color, 3));
			    } else {
			    	frame=0;
			    	setForeground(Color.GRAY);
//			    	if(myRedLine!=null)myRedLine.setBorder(new LineBorder(Environment.fh_CI_Color, 1));
				}
			    repaint();
		    } else {
		    	stop();
		    }

		    counter++;

		    // Delay for a while
		    try {
		    	Thread.sleep(delay);
		    } catch (InterruptedException e) {
		    	break;
		    }
	
		}
    }

    public void stop() {
    	animator = null;
    	setForeground(Color.GRAY);
//    	if(myRedLine!=null)myRedLine.setBorder(new LineBorder(Environment.fh_CI_Color, 1));
    }
}